import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import UserPage from 'flarum/forum/components/UserPage';
import LinkButton from 'flarum/common/components/LinkButton';
import CustomProfilePageComponent from './pages/CustomProfilePage';

export default function addUserProfilePage() {
  // Route kaydet - CLOSURE içinde dinamik olarak slug'ı al
  app.routes['user.customPage'] = {
    path: '/u/:username/:slug',
    component: CustomProfilePageComponent,
    resolverClass: class {
      onmatch(args: any) {
        // URL'den slug parametresini al veya varsayılanı kullan
        const urlSlug = app.forum?.attribute('huseyinfiliz-custom-profile-page.url_slug') || 'customPage';
        
        // Eğer URL'deki slug bizim slug'ımız değilse, bu route'u reddet
        if (args.slug !== urlSlug) {
          return;
        }
        
        return CustomProfilePageComponent;
      }
    }
  };

  // Navigation ekle
  extend(UserPage.prototype, 'navItems', function (items) {
    const user = this.user;
    if (!user) return;

    // ✅ Permission kontrolü ekle
    const canView = app.forum?.attribute('canViewCustomPage');
    const canEdit = app.session.user?.id() === user.id() && 
                   app.forum?.attribute('canEditOwnCustomPage');

    // Eğer görüntüleme izni yoksa ve kendi sayfası da değilse tab'ı gösterme
    if (!canView && !canEdit) {
      return;
    }

    // Dinamik olarak her çağrıda slug'ı al
    const urlSlug = app.forum?.attribute('huseyinfiliz-custom-profile-page.url_slug') || 'customPage';
    const tabTitle = app.forum?.attribute('huseyinfiliz-custom-profile-page.tab_title') || 'Custom Page';
    const tabIcon = app.forum?.attribute('huseyinfiliz-custom-profile-page.tab_icon') || 'fas fa-file-alt';

    items.add(
      'customPageLink',
      LinkButton.component(
        {
          href: app.route('user.customPage', { username: user.slug(), slug: urlSlug }),
          name: 'custompage',
          icon: tabIcon,
        },
        tabTitle
      ),
      85
    );
  });
}