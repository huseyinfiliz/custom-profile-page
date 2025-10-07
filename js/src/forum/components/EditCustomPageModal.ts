import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import TextEditor from 'flarum/common/components/TextEditor';  // ✅ Flarum editörü
import Stream from 'flarum/common/utils/Stream';

export default class EditCustomPageModal extends Modal {
  contentText!: Stream<string>;  // ✅ İsim değişti (content yerine contentText)
  loading: boolean = false;
  useEditor: boolean = false;     // ✅ Editör kullanılacak mı?

  oninit(vnode: any) {
    super.oninit(vnode);
    
    // İçeriği Stream olarak başlat
    this.contentText = Stream(this.attrs.customPage?.content() || '');
    
    // Format kontrolü - markdown ise editör kullan
    const format = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.content_format') || 'markdown';
    this.useEditor = format === 'markdown';
  }

  className() {
    return 'EditCustomPageModal Modal--large';
  }

  title() {
    return app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.title');
  }

  content() {  // ✅ Modal'ın content metodu
    return m('.Modal-body', [
      m('.Form', [
        m('.Form-group', [
          m('label', app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_label')),
          
          // ✅ Markdown ise Flarum editörünü kullan
          this.useEditor ? this.renderEditor() : this.renderTextarea()
        ]),

        m('.Form-group', [
          Button.component(
            {
              type: 'submit',
              className: 'Button Button--primary',
              loading: this.loading,
              disabled: this.loading,
            },
            app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.submit_button')
          )
        ])
      ])
    ]);
  }

  renderEditor() {
    // ✅ Flarum'un TextEditor'ünü kullan
    return m(TextEditor, {
      composer: this,
      submitLabel: app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.submit_button'),
      placeholder: app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_placeholder'),
      onchange: (value: string) => {
        this.contentText(value);
      },
      onsubmit: () => {
        this.onsubmit(new Event('submit') as any);
      },
      value: this.contentText(),
      disabled: this.loading,
    });
  }

  renderTextarea() {
    // ✅ Basit textarea (HTML veya Text formatı için)
    return m('textarea.FormControl', {
      rows: 15,
      value: this.contentText(),
      oninput: (e: any) => {
        this.contentText(e.target.value);
      },
      placeholder: app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_placeholder'),
      disabled: this.loading,
    });
  }

  onsubmit(e: Event) {
    e.preventDefault();

    if (this.loading) return;

    this.loading = true;

    const userId = this.attrs.user.id();
    const data = {
      type: 'custom-profile-pages',
      attributes: {
        content: this.contentText(),  // ✅ Stream değeri
      },
    };

    const method = this.attrs.customPage ? 'PATCH' : 'POST';
    const url = `${app.forum.attribute('apiUrl')}/users/${userId}/custom-page`;

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
      .catch((error) => {
        console.error('Save error:', error);
        this.loading = false;
        
        // Hata mesajı göster
        app.alerts.show(
          { type: 'error' },
          app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.error')
        );
        
        m.redraw();
      });
  }
}