import app from 'flarum/admin/app';

app.initializers.add('huseyinfiliz/custom-profile-page', () => {
  app.extensionData
    .for('huseyinfiliz-custom-profile-page')
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.tab_title',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.tab_title'),
      type: 'text',
      default: 'Custom Page',
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.tab_icon',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.tab_icon'),
      type: 'text',
      placeholder: 'fas fa-file-alt',
      help: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.tab_icon_help'),
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.url_slug',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.url_slug'),
      type: 'text',
      placeholder: 'customPage',
      default: 'customPage',
      help: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.url_slug_help'),
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.content_format',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.content_format'),
      type: 'select',
      options: {
        markdown: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.format_markdown'),
        html: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.format_html'),
        text: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.format_text'),
      },
      default: 'markdown',
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.allowed_html_tags',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.allowed_html_tags'),
      type: 'text',
      placeholder: 'b,i,u,strong,em,a,br,p,ul,ol,li,blockquote,code,pre',
      help: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.allowed_html_tags_help'),
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.default_template',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.default_template'),
      type: 'textarea',
	  rows: 20,
      placeholder: '',
      help: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.default_template_help'),
    })
    .registerSetting({
      setting: 'huseyinfiliz-custom-profile-page.enable_media_embeds',
      label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.enable_media_embeds'),
      type: 'boolean',
      help: app.translator.trans('huseyinfiliz-custom-profile-page.admin.settings.enable_media_embeds_help'),
      default: true,
    })
    .registerPermission(
      {
        icon: 'fas fa-edit',
        label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.permissions.edit_own'),
        permission: 'user.editOwnCustomPage',
      },
      'start',
      95
    )
    .registerPermission(
      {
        icon: 'fas fa-eye',
        label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.permissions.view'),
        permission: 'user.viewCustomPage',
        allowGuest: true
      },
      'view',
      95
    )
    .registerPermission(
      {
        icon: 'fas fa-user-shield',
        label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.permissions.moderate'),
        permission: 'user.moderateCustomPage',
      },
      'moderate',
      95
    );
});