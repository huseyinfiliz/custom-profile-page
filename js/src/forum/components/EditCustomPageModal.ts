import app from 'flarum/forum/app';
import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import User from 'flarum/common/models/User';
import CustomProfilePage from '../models/CustomProfilePage';

interface EditCustomPageModalAttrs extends IInternalModalAttrs {
  user: User;
  customPage: CustomProfilePage | null;
  onSave: () => void;
}

export default class EditCustomPageModal extends Modal<EditCustomPageModalAttrs> {
  content: string = '';
  loading: boolean = false;

  oninit(vnode: any) {
    super.oninit(vnode);
    this.content = this.attrs.customPage?.content() || '';
  }

  className() {
    return 'EditCustomPageModal Modal--large';
  }

  title() {
    return app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.title');
  }

  content() {
    return m('.Modal-body', 
      m('.Form', [
        m('.Form-group', [
          m('label', app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_label')),
          m('textarea.FormControl', {
            rows: 15,
            value: this.content,
            oninput: (e: any) => (this.content = e.target.value),
            placeholder: app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_placeholder')
          })
        ]),

        m('.Form-group',
          Button.component(
            {
              type: 'submit',
              className: 'Button Button--primary',
              loading: this.loading,
            },
            app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.submit_button')
          )
        )
      ])
    );
  }

  onsubmit(e: Event) {
    e.preventDefault();

    this.loading = true;

    const userId = this.attrs.user.id()!;
    const data = {
      type: 'custom-profile-pages',
      attributes: {
        content: this.content,
      },
    };

    const method = this.attrs.customPage ? 'PATCH' : 'POST';
    const url = this.attrs.customPage
      ? `${app.forum.attribute('apiUrl')}/users/${userId}/custom-page`
      : `${app.forum.attribute('apiUrl')}/users/${userId}/custom-page`;

    app.request({
      method,
      url,
      body: { data },
    })
      .then(() => {
        this.loading = false;
        this.hide();
        this.attrs.onSave();
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        m.redraw();
      });
  }
}
