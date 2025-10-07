import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';
import UserPage from 'flarum/forum/components/UserPage';
import LinkButton from 'flarum/common/components/LinkButton';
import CustomProfilePage from './models/CustomProfilePage';
import CustomProfilePageTab from './components/CustomProfilePageTab';

app.initializers.add('huseyinfiliz/custom-profile-page', () => {
  // Model'i kaydet
  app.store.models['custom-profile-pages'] = CustomProfilePage;

  // User sayfasına yeni tab ekle
  extend(UserPage.prototype, 'navItems', function (items) {
    const user = this.user;
    const canView = app.forum.attribute('canViewCustomPage');
    const canEdit = app.session.user?.id() === user?.id() && app.forum.attribute('canEditOwnCustomPage');

    // Eğer görüntüleme yetkisi varsa veya kendi sayfası ise tab'ı göster
    if (canView || canEdit) {
      const tabTitle = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.tab_title') || 'Custom Page';
      const tabIcon = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.tab_icon') || 'fas fa-file-alt';

      items.add(
        'customPage',
        LinkButton.component(
          {
            href: app.route('user.customPage', { username: user?.slug() }),
            icon: tabIcon,
          },
          tabTitle
        ),
        10
      );
    }
  });

  // User sayfasına yeni route ekle
  extend(UserPage.prototype, 'content', function (content) {
    if (this.attrs.routeName === 'user.customPage') {
      return m(CustomProfilePageTab, { user: this.user });
    }
  });

  // Route'u kaydet
  app.routes['user.customPage'] = {
    path: '/u/:username/custom-page',
    component: UserPage,
    resolverClass: UserPage,
  };
});