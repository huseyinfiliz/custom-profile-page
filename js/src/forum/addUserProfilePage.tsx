import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserPage from 'flarum/forum/components/UserPage';
import LinkButton from 'flarum/common/components/LinkButton';
import CustomProfilePageComponent from './pages/CustomProfilePage';

export default function addUserProfilePage() {
  // Route kaydet
  app.routes['user.customPage'] = {
    path: '/u/:username/customPage',
    component: CustomProfilePageComponent,
  };

  // Navigation ekle
  extend(UserPage.prototype, 'navItems', function (items) {
    const user = this.user;
    if (!user) return;

    const canView = app.forum.attribute('canViewCustomPage');
    const canEdit = app.session.user?.id() === user.id() && 
                   app.forum.attribute('canEditOwnCustomPage');

    if (canView || canEdit) {
      const tabTitle = app.forum.attribute('huseyinfiliz-custom-profile-page.tab_title') || 'Custom Page';
      const tabIcon = app.forum.attribute('huseyinfiliz-custom-profile-page.tab_icon') || 'fas fa-file-alt';

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
    }
  });
}