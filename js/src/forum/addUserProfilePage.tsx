import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserPage from 'flarum/forum/components/UserPage';
import LinkButton from 'flarum/common/components/LinkButton';
import CustomProfilePageComponent from './pages/CustomProfilePage';

export default function addUserProfilePage() {
  // Ayarları güvenli bir şekilde bulmak için yardımcı fonksiyon
  const getUrlSlug = () => {
    const attributeName = 'huseyinfiliz-custom-profile-page.url_slug';

    // 1. Durum: app.forum hazırdır (Sayfa geçişlerinde vs.)
    if (app.forum) {
      return app.forum.attribute(attributeName);
    }

    // 2. Durum: app.forum hazır değil, ham veriye (Payload) bakacağız.
    const data = app.data as any;

    // A) Ana dökümana bak (Anasayfadaysak buradadır)
    if (data.apiDocument?.data?.type === 'forums') {
      return data.apiDocument.data.attributes?.[attributeName];
    }

    // B) Resources dizisine bak (Profil sayfasındaysak "forums" buraya saklanmıştır)
    // Önceki debug kodunuzda burası çalışmıştı.
    if (Array.isArray(data.resources)) {
      const forumResource = data.resources.find((r: any) => r.type === 'forums');
      if (forumResource?.attributes) {
        return forumResource.attributes[attributeName];
      }
    }

    // C) Hiçbiri yoksa
    return null;
  };

  // Ayarı çek, yoksa varsayılanı kullan
  const settingSlug = getUrlSlug();
  const urlSlug = settingSlug || 'customPage';

  // Rotayı oluştur (Artık "test" veya ayarlı kelime neyse o gelecek)
  app.routes['user.customPage'] = {
    path: `/u/:username/${urlSlug}`,
    component: CustomProfilePageComponent,
  };

  // Kullanıcı profili menüsüne butonu ekle
  extend(UserPage.prototype, 'navItems', function (items) {
    const user = this.user;
    if (!user) return;

    // Yetki kontrolleri
    const canView = app.forum?.attribute('canViewCustomPage');
    const canEdit = app.session.user?.id() === user.id() && app.forum?.attribute('canEditOwnCustomPage');

    if (!canView && !canEdit && !app.forum?.attribute('canModerateCustomPage')) {
      return;
    }

    const tabTitle = app.forum?.attribute('huseyinfiliz-custom-profile-page.tab_title') || 'Custom Page';
    const tabIcon = app.forum?.attribute('huseyinfiliz-custom-profile-page.tab_icon') || 'fas fa-file-alt';

    items.add(
      'customPageLink',
      LinkButton.component(
        {
          href: app.route('user.customPage', { username: user.slug() }),
          name: 'custompage',
          icon: tabIcon,
        },
        tabTitle
      ),
      85
    );
  });
}
