<?php

namespace HuseyinFiliz\CustomProfilePage\Tests\Integration;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\DataProvider;

class CustomPageApiTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension('huseyinfiliz-custom-profile-page');
        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                array_merge($this->normalUser(), ['id' => 3, 'username' => 'other', 'email' => 'other@machine.local']),
                array_merge($this->normalUser(), ['id' => 4, 'username' => 'moderator', 'email' => 'mod@machine.local']),
            ],
            'group_user' => [
                ['user_id' => 1, 'group_id' => 1],
                ['user_id' => 4, 'group_id' => 4],
            ],
            'custom_profile_pages' => [
                ['id' => 1, 'user_id' => 3, 'content' => 'Existing page', 'created_at' => '2026-01-01 00:00:00', 'updated_at' => '2026-01-01 00:00:00'],
            ],
        ]);
    }

    private function callApi(string $method, string $path, ?int $actor = null, array $attributes = [], ?string $id = null)
    {
        $options = $actor === null ? [] : ['authenticatedAs' => $actor];
        $request = $this->request($method, '/api'.parse_url($path, PHP_URL_PATH), $options)->withAttribute('bypassCsrfToken', true);
        parse_str(parse_url($path, PHP_URL_QUERY) ?? '', $query);
        $request = $request->withQueryParams($query);
        if ($method !== 'GET' && $method !== 'DELETE') {
            $data = ['type' => 'custom-profile-pages', 'attributes' => $attributes];
            if ($id !== null) {
                $data['id'] = $id;
            }
            $request = $this->requestWithJsonBody($request, ['data' => $data]);
        }

        return $this->send($request);
    }

    public static function createCases(): array
    {
        return [
            'owner legacy' => ['/users/2/custom-page', 2, [], 201],
            'other legacy' => ['/users/2/custom-page', 3, [], 403],
            'moderator legacy' => ['/users/2/custom-page', 4, [], 201],
            'guest legacy' => ['/users/2/custom-page', null, [], 403],
            'owner resource' => ['/custom-profile-pages', 2, [], 201],
            'owner explicit resource' => ['/custom-profile-pages', 2, ['userId' => 2], 201],
            'other resource' => ['/custom-profile-pages', 3, ['userId' => 2], 403],
            'moderator resource' => ['/custom-profile-pages', 4, ['userId' => 2], 201],
            'guest resource' => ['/custom-profile-pages', null, ['userId' => 2], 401],
        ];
    }

    #[DataProvider('createCases')]
    public function test_create_authorizes_the_target_owner(string $path, ?int $actor, array $attributes, int $status): void
    {
        $response = $this->callApi('POST', $path, $actor, $attributes + ['content' => 'Created']);
        $this->assertSame($status, $response->getStatusCode(), (string) $response->getBody());
        if ($status === 201) {
            $body = json_decode((string) $response->getBody(), true);
            $this->assertSame(2, $body['data']['attributes']['userId']);
            $this->assertSame('Created', $body['data']['attributes']['content']);
        }
    }

    public static function updateCases(): array
    {
        return [
            'owner legacy' => ['/users/3/custom-page', 3, 200],
            'other legacy' => ['/users/3/custom-page', 2, 403],
            'moderator legacy' => ['/users/3/custom-page', 4, 200],
            'guest legacy' => ['/users/3/custom-page', null, 403],
            'owner resource' => ['/custom-profile-pages/1', 3, 200],
            'other resource' => ['/custom-profile-pages/1', 2, 403],
            'moderator resource' => ['/custom-profile-pages/1', 4, 200],
        ];
    }

    #[DataProvider('updateCases')]
    public function test_update_checks_ownership(string $path, ?int $actor, int $status): void
    {
        $response = $this->callApi('PATCH', $path, $actor, ['content' => 'Updated'], '1');
        $this->assertSame($status, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame($status === 200 ? 'Updated' : 'Existing page', $this->database()->table('custom_profile_pages')->where('id', 1)->value('content'));
    }

    public function test_guests_can_view_through_both_apis_and_relationship_includes(): void
    {
        foreach (['/users/3/custom-page', '/custom-profile-pages/1', '/custom-profile-pages', '/users/3?include=customPage'] as $path) {
            $response = $this->callApi('GET', $path);
            $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
            $this->assertStringContainsString('Existing page', (string) $response->getBody());
        }
    }

    public function test_user_without_a_page_returns_a_successful_empty_response(): void
    {
        foreach ([null, 2, 4] as $actor) {
            $response = $this->callApi('GET', '/users/2/custom-page', $actor);
            $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
            $this->assertSame(['data' => null], json_decode((string) $response->getBody(), true));
        }
        $this->assertFalse($this->database()->table('custom_profile_pages')->where('user_id', 2)->exists());
    }

    public function test_empty_page_still_checks_user_existence_and_view_permission(): void
    {
        $this->database()->table('group_permission')->where('permission', 'user.viewCustomPage')->delete();
        $this->assertSame(404, $this->callApi('GET', '/users/999/custom-page')->getStatusCode());
        $this->assertSame(403, $this->callApi('GET', '/users/2/custom-page')->getStatusCode());
        $this->assertSame(200, $this->callApi('GET', '/users/2/custom-page', 2)->getStatusCode());
    }

    public function test_missing_view_permission_scopes_lists_and_includes(): void
    {
        $this->database()->table('group_permission')->where('permission', 'user.viewCustomPage')->delete();
        $this->assertSame(403, $this->callApi('GET', '/users/3/custom-page')->getStatusCode());
        $this->assertSame(404, $this->callApi('GET', '/custom-profile-pages/1')->getStatusCode());
        foreach (['/custom-profile-pages', '/users/3?include=customPage'] as $path) {
            $response = $this->callApi('GET', $path);
            $this->assertSame(200, $response->getStatusCode());
            $this->assertStringNotContainsString('Existing page', (string) $response->getBody());
        }
    }

    public function test_private_forum_content_is_not_exposed_to_guests(): void
    {
        $this->database()->table('group_permission')->where('permission', 'viewForum')->where('group_id', 2)->delete();
        foreach (['/users/3/custom-page', '/custom-profile-pages/1', '/custom-profile-pages', '/users/3?include=customPage'] as $path) {
            $response = $this->callApi('GET', $path);
            $this->assertStringNotContainsString('Existing page', (string) $response->getBody());
        }
    }

    public function test_moderation_without_general_view_permission_is_consistent(): void
    {
        $this->database()->table('group_permission')->where('permission', 'user.viewCustomPage')->delete();
        foreach (['/users/3/custom-page', '/custom-profile-pages/1', '/custom-profile-pages', '/users/3?include=customPage'] as $path) {
            $response = $this->callApi('GET', $path, 4);
            $this->assertSame(200, $response->getStatusCode(), (string) $response->getBody());
            $this->assertStringContainsString('Existing page', (string) $response->getBody());
        }
    }

    public function test_revoked_edit_permission_prevents_creation(): void
    {
        $this->database()->table('group_permission')->where('permission', 'user.editOwnCustomPage')->delete();
        foreach (['/users/2/custom-page', '/custom-profile-pages'] as $path) {
            $this->assertSame(403, $this->callApi('POST', $path, 2, ['content' => 'Denied'])->getStatusCode());
        }
    }

    public function test_duplicate_pages_and_missing_users_return_controlled_errors(): void
    {
        foreach (['/users/3/custom-page', '/custom-profile-pages'] as $path) {
            $response = $this->callApi('POST', $path, 1, ['userId' => 3, 'content' => 'Duplicate']);
            $this->assertSame(422, $response->getStatusCode(), (string) $response->getBody());
        }
        $this->assertSame(404, $this->callApi('POST', '/users/999/custom-page', 1)->getStatusCode());
        $this->assertSame(422, $this->callApi('POST', '/custom-profile-pages', 1, ['userId' => 999])->getStatusCode());
    }

    public function test_invalid_content_is_rejected_by_both_apis(): void
    {
        foreach (['/users/2/custom-page', '/custom-profile-pages'] as $path) {
            foreach ([['nested'], str_repeat('x', 65536)] as $content) {
                $response = $this->callApi('POST', $path, 2, ['content' => $content]);
                $this->assertSame(422, $response->getStatusCode(), (string) $response->getBody());
            }
        }
    }

    public function test_ownership_cannot_be_reassigned(): void
    {
        $response = $this->callApi('PATCH', '/custom-profile-pages/1', 3, ['userId' => 2], '1');
        $this->assertSame(403, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame(3, (int) $this->database()->table('custom_profile_pages')->where('id', 1)->value('user_id'));
    }

    public function test_upgrade_does_not_restore_revoked_permissions(): void
    {
        $this->database()->table('group_permission')->where('permission', 'like', '%CustomPage')->delete();
        $migration = require __DIR__.'/../../migrations/2025_01_01_000000_create_custom_profile_pages_table.php';
        $migration['up']($this->database()->getSchemaBuilder());
        $this->assertSame(0, $this->database()->table('group_permission')->where('permission', 'like', '%CustomPage')->count());
    }

    public function test_owner_can_read_without_general_view_permission(): void
    {
        $this->database()->table('group_permission')->where('permission', 'user.viewCustomPage')->delete();
        foreach (['/users/3/custom-page', '/custom-profile-pages/1'] as $path) {
            $this->assertSame(200, $this->callApi('GET', $path, 3)->getStatusCode());
        }
    }

    public function test_user_filter_scopes_the_resource_list(): void
    {
        $response = $this->callApi('GET', '/custom-profile-pages?filter[user]=2');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], json_decode((string) $response->getBody(), true)['data']);
    }

    public static function deleteCases(): array
    {
        return ['owner' => [3, 204], 'other' => [2, 403], 'moderator' => [4, 204], 'guest' => [null, 403]];
    }

    #[DataProvider('deleteCases')]
    public function test_delete_checks_ownership(?int $actor, int $status): void
    {
        $response = $this->callApi('DELETE', '/custom-profile-pages/1', $actor);
        $this->assertSame($status, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame($status !== 204, $this->database()->table('custom_profile_pages')->where('id', 1)->exists());
    }
}
