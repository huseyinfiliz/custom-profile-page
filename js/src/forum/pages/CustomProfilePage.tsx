import app from 'flarum/forum/app';
import UserPage from 'flarum/forum/components/UserPage';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Button from 'flarum/common/components/Button';
import EditCustomPageModal from '../components/EditCustomPageModal';

export default class CustomProfilePage extends UserPage {
    loading: boolean = false;
    customPage: any = null;
    error: string | null = null;

    oninit(vnode) {
        super.oninit(vnode);
        this.loading = true;
        this.loadUser(this.attrs.username);
        
        // Debug
        console.log('CustomProfilePage initialized', this.attrs);
    }

    oncreate(vnode) {
        super.oncreate(vnode);
        
        // User yüklendikten sonra custom page'i yükle
        if (this.user) {
            this.loadCustomPage();
        } else {
            // User henüz yüklenmemişse bekle
            setTimeout(() => {
                if (this.user) {
                    this.loadCustomPage();
                }
            }, 100);
        }
    }

    content() {
        // User kontrolü
        if (!this.user) {
            console.log('Waiting for user...');
            return m('.CustomProfilePage', m(LoadingIndicator));
        }

        if (this.loading) {
            return m('.CustomProfilePage', [
                m('h2', this.user.displayName() + "'s Custom Page"),
                m(LoadingIndicator)
            ]);
        }

        if (this.error) {
            return m('.CustomProfilePage', 
                m('.Alert.Alert--error', this.error)
            );
        }

        const canEdit = app.session.user?.id() === this.user.id() && 
                       app.forum.attribute('canEditOwnCustomPage');
        const content = this.customPage?.content();
        const format = app.forum.attribute('huseyinfiliz-custom-profile-page.content_format') || 'markdown';

        console.log('Rendering content', { content, format, canEdit });

        return m('.CustomProfilePage', [
            canEdit && m('.CustomProfilePage-actions',
                m(Button, {
                    className: 'Button Button--primary',
                    icon: 'fas fa-edit',
                    onclick: () => {
                        console.log('Opening edit modal');
                        app.modal.show(EditCustomPageModal, {
                            user: this.user,
                            customPage: this.customPage,
                            onSave: () => {
                                console.log('Modal saved, reloading');
                                this.loadCustomPage();
                            }
                        });
                    }
                }, app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.edit_button'))
            ),

            m('.CustomProfilePage-content',
                content ? this.renderContent(content, format) : this.renderEmpty(canEdit)
            )
        ]);
    }

    loadCustomPage() {
        if (!this.user) {
            console.log('No user yet, waiting...');
            setTimeout(() => this.loadCustomPage(), 100);
            return;
        }

        this.loading = true;
        this.error = null;

        console.log('Loading custom page for user:', this.user.id());

        app.request({
            method: 'GET',
            url: app.forum.attribute('apiUrl') + '/users/' + this.user.id() + '/custom-page',
        })
        .then((response) => {
            console.log('Custom page loaded:', response);
            if (response.data) {
                this.customPage = app.store.pushPayload(response);
            }
            this.loading = false;
            m.redraw();
        })
        .catch((error) => {
            console.error('Custom page load error:', error);
            if (error.status === 404) {
                console.log('No custom page yet (404)');
                this.customPage = null;
            } else {
                console.error('Real error:', error);
                this.error = 'Failed to load custom page';
            }
            this.loading = false;
            m.redraw();
        });
    }

    renderContent(text, format) {
        if (format === 'html') {
            return m.trust(text);
        }
        
        if (format === 'markdown') {
            const html = text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
            return m.trust(html);
        }
        
        return m('pre', text);
    }

    renderEmpty(canEdit) {
        return m('.CustomProfilePage-empty', [
            m('p', app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.empty')),
            canEdit && m('p.help-text', 'Click "Edit" to create your custom page')
        ]);
    }
}