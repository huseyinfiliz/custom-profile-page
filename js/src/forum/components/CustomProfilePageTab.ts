import app from 'flarum/forum/app';
import Component, { ComponentAttrs } from 'flarum/common/Component';
import User from 'flarum/common/models/User';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Button from 'flarum/common/components/Button';
import EditCustomPageModal from './EditCustomPageModal';
import CustomProfilePage from '../models/CustomProfilePage';

interface CustomProfilePageTabAttrs extends ComponentAttrs {
  user: User;
}

export default class CustomProfilePageTab extends Component<CustomProfilePageTabAttrs> {
  loading: boolean = false;
  customPage: CustomProfilePage | null = null;

  oninit(vnode: any) {
    super.oninit(vnode);
    this.load();
  }

  load() {
    const user = this.attrs.user;
    
    this.loading = true;
    
    app.store
      .find<CustomProfilePage>('custom-profile-pages', user.id()!)
      .then((page) => {
        this.customPage = page;
        this.loading = false;
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        m.redraw();
      });
  }

  view() {
    const user = this.attrs.user;
    const canEdit = app.session.user?.id() === user.id() && app.forum.attribute('canEditOwnCustomPage');

    if (this.loading) {
      return m(LoadingIndicator);
    }

    const content = this.customPage?.content();
    const format = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.content_format') || 'markdown';

    return m('.CustomProfilePage', [
      canEdit && m('.CustomProfilePage-actions',
        m(Button, {
          className: 'Button Button--primary',
          icon: 'fas fa-edit',
          onclick: () => app.modal.show(EditCustomPageModal, { 
            user, 
            customPage: this.customPage, 
            onSave: () => this.load() 
          })
        }, app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.edit_button'))
      ),

      m('.CustomProfilePage-content',
        content ? this.renderContent(content, format) : this.renderEmpty(canEdit)
      )
    ]);
  }

  renderContent(content: string, format: string) {
    if (format === 'html') {
      return m.trust(content);
    } else if (format === 'markdown') {
      // Basit markdown rendering - gerçek uygulamada marked.js veya benzeri kullanılabilir
      const html = content
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/\n/g, '<br>');
      return m.trust(html);
    } else {
      return m('pre', content);
    }
  }

  renderEmpty(canEdit: boolean) {
    return m('.CustomProfilePage-empty',
      app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.empty')
    );
  }
}