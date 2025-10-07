<?php

namespace HuseyinFiliz\CustomProfilePage\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Http\RequestUtil;
use Flarum\User\User;
use HuseyinFiliz\CustomProfilePage\Api\Serializer\CustomProfilePageSerializer;
use HuseyinFiliz\CustomProfilePage\CustomProfilePage;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowCustomPageController extends AbstractShowController
{
    public $serializer = CustomProfilePageSerializer::class;

    protected function data(ServerRequestInterface $request, Document $document)
    {
        // Route parametresinden user ID'yi al
        $userId = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        
        // Kullanıcıyı bul
        $user = User::findOrFail($userId);
        
        // Custom page'i bul
        $page = CustomProfilePage::where('user_id', $userId)->first();
        
        // Sayfa yoksa ve görüntüleme yetkisi varsa null dön (404 değil)
        if (!$page) {
            // Kendi sayfasını görüntülüyorsa veya viewCustomPage yetkisi varsa null döndür
            if ($actor->id == $userId || $actor->hasPermission('user.viewCustomPage')) {
                return null;
            }
            
            // Yoksa 404
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }
        
        // Görüntüleme yetkisi kontrolü
        // Kendi sayfası mı veya viewCustomPage yetkisi var mı?
        if ($actor->id != $userId && !$actor->hasPermission('user.viewCustomPage')) {
            throw new \Flarum\User\Exception\PermissionDeniedException();
        }
        
        return $page;
    }
}