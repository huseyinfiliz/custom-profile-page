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
    .registerPermission(
      {
        icon: 'fas fa-edit',
        label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.permissions.edit_own'),
        permission: 'user.editOwnCustomPage',
      },
      'start',    // ✅ 'start' grup
      95          // ✅ Priority
    )
    .registerPermission(
      {
        icon: 'fas fa-eye',
        label: app.translator.trans('huseyinfiliz-custom-profile-page.admin.permissions.view'),
        permission: 'user.viewCustomPage',
      },
      'view',     // ✅ 'view' grup
      95          // ✅ Priority
    );
});