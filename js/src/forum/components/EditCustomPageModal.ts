import app from 'flarum/forum/app';
import Modal from 'flarum/common/components/Modal';
import Button from 'flarum/common/components/Button';
import Stream from 'flarum/common/utils/Stream';

export default class EditCustomPageModal extends Modal {
  contentText!: Stream<string>;
  loading: boolean = false;
  textareaElement: HTMLTextAreaElement | null = null;

  oninit(vnode: any) {
    super.oninit(vnode);
    
    this.contentText = Stream(this.attrs.customPage?.content() || '');
  }

  className() {
    return 'EditCustomPageModal Modal--large';
  }

  title() {
    return app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.title');
  }

  content() {
    const format = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.content_format') || 'markdown';
    const isMarkdown = format === 'markdown';

    return m('.Modal-body', [
      m('.Form', [
        // Format bilgisi
        this.renderFormatInfo(format),
        
        // Markdown toolbar
        isMarkdown && this.renderMarkdownToolbar(),
        
        m('.Form-group', [
          this.renderTextarea()
        ]),

        m('.Form-group.EditCustomPageModal-actions', [
          Button.component(
            {
              type: 'submit',
              className: 'Button Button--primary',
              loading: this.loading,
              disabled: this.loading,
              onclick: this.onsubmit.bind(this),
            },
            app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.submit_button')
          ),
          ' ',
          Button.component(
            {
              className: 'Button',
              disabled: this.loading,
              onclick: () => this.hide(),
            },
            app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.cancel_button')
          )
        ])
      ])
    ]);
  }

  renderFormatInfo(format: string) {
    let icon = '';
    let text = '';
    
    if (format === 'markdown') {
      icon = 'fab fa-markdown';
      text = 'You can use Markdown formatting: **bold**, *italic*, [link](url), `code`, etc.';
    } else if (format === 'html') {
      const allowedTags = app.forum.attribute<string>('huseyinfiliz-custom-profile-page.allowed_html_tags') || '';
      icon = 'fab fa-html5';
      text = `You can use the following HTML tags: ${allowedTags}`;
    } else {
      icon = 'fas fa-align-left';
      text = 'Plain text only. No HTML or Markdown formatting allowed.';
    }

    return m('.Alert.EditCustomPageModal-formatInfo', [
      m('i.icon', { className: icon }),
      m('span', text)
    ]);
  }

  renderMarkdownToolbar() {
    return m('.MarkdownToolbar.ButtonGroup', [
      // Header
      this.toolbarButton('Heading', 'fas fa-heading', '\n## ', ''),
      
      // Bold
      this.toolbarButton('Bold', 'fas fa-bold', '**', '**'),
      
      // Italic
      this.toolbarButton('Italic', 'fas fa-italic', '*', '*'),
      
      // Strikethrough
      this.toolbarButton('Strikethrough', 'fas fa-strikethrough', '~~', '~~'),
      
      // Quote
      this.toolbarButton('Quote', 'fas fa-quote-left', '\n> ', ''),
      
      // Spoiler
      this.toolbarButton('Spoiler', 'fas fa-eye-slash', '>!', '!<'),
      
      // Code
      this.toolbarButton('Code', 'fas fa-code', '`', '`'),
      
      // Link
      this.toolbarButton('Link', 'fas fa-link', '[', '](url)'),
      
      // Image
      this.toolbarButton('Image', 'fas fa-image', '![', '](image-url)'),
      
      // Bulleted List
      this.toolbarButton('Bulleted List', 'fas fa-list-ul', '\n- ', ''),
      
      // Numbered List
      this.toolbarButton('Numbered List', 'fas fa-list-ol', '\n1. ', ''),
      
      // Mention
      this.toolbarButton('Mention', 'fas fa-at', '@', ''),
    ]);
  }

  toolbarButton(name: string, icon: string, before: string, after: string) {
    return m('button.Button.Button--icon.Button--link', {
      type: 'button',
      title: name,
      onclick: (e: Event) => {
        e.preventDefault();
        this.insertMarkdown(before, after);
      },
    }, m('i', { className: icon }));
  }

  insertMarkdown(before: string, after: string) {
    if (!this.textareaElement) return;

    const textarea = this.textareaElement;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = this.contentText();
    const selectedText = text.substring(start, end);

    const newText = text.substring(0, start) + before + selectedText + after + text.substring(end);
    
    this.contentText(newText);
    
    setTimeout(() => {
      const newCursorPos = start + before.length + selectedText.length;
      textarea.focus();
      textarea.setSelectionRange(newCursorPos, newCursorPos);
      m.redraw();
    }, 0);
  }

  renderTextarea() {
    return m('textarea.FormControl', {
      rows: 20,
      value: this.contentText(),
      oninput: (e: any) => {
        this.contentText(e.target.value);
      },
      oncreate: (vnode: any) => {
        this.textareaElement = vnode.dom;
      },
      placeholder: app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.content_placeholder'),
      disabled: this.loading,
    });
  }

  onsubmit(e?: Event) {
    if (e) e.preventDefault();
    if (this.loading) return;

    this.loading = true;

    const userId = this.attrs.user.id();
    const data = {
      type: 'custom-profile-pages',
      attributes: {
        content: this.contentText(),
      },
    };

    const method = this.attrs.customPage ? 'PATCH' : 'POST';
    const url = `${app.forum.attribute('apiUrl')}/users/${userId}/custom-page`;

    app.request({
      method,
      url,
      body: { data },
    })
      .then((response) => {
        this.loading = false;
        
        if (response.data) {
          const page = app.store.pushPayload(response);
          if (this.attrs.onSave) {
            this.attrs.onSave(page);
          }
        }
        
        this.hide();
        m.redraw();
      })
      .catch(() => {
        this.loading = false;
        
        app.alerts.show(
          { type: 'error' },
          app.translator.trans('huseyinfiliz-custom-profile-page.forum.edit_modal.error')
        );
        
        m.redraw();
      });
  }
}