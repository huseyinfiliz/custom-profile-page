import UserPage from 'flarum/forum/components/UserPage';
import app from 'flarum/forum/app';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';
import Button from 'flarum/common/components/Button';
import EditCustomPageModal from '../components/EditCustomPageModal';

export default class CustomProfilePage extends UserPage {
    loading: boolean = true;
    error: string | null = null;
    customPage: any = null;
    permissionDenied: boolean = false;

    oninit(vnode) {
        super.oninit(vnode);

        this.loading = true;
        this.error = null;
        this.customPage = null;
        this.permissionDenied = false;

        this.loadUser(m.route.param('username'));
    }

    show(user) {
        super.show(user);
        this.loadCustomPage();
    }

    oncreate(vnode) {
        super.oncreate(vnode);
        this.processMediaLinks();
    }

    onupdate(vnode) {
        super.onupdate(vnode);
        this.processMediaLinks();
    }

    // ✅ YouTube ve diğer media linklerini iframe'e çevir
    processMediaLinks() {
        // ✅ Admin ayarını kontrol et
        const enableMediaEmbeds = app.forum.attribute('huseyinfiliz-custom-profile-page.enable_media_embeds');
        
        if (!enableMediaEmbeds) {
            return; // Embed kapalıysa hiçbir şey yapma
        }

        setTimeout(() => {
            const content = this.element?.querySelector('.CustomProfilePage-content');
            if (!content) return;

            // YouTube linklerini bul ve iframe'e çevir
            const links = content.querySelectorAll('a[href*="youtube.com"], a[href*="youtu.be"]');
            
            links.forEach((link) => {
                // Zaten işlenmiş mi kontrol et
                if (link.classList.contains('media-processed')) return;
                
                const url = link.getAttribute('href');
                if (!url) return;

                // YouTube video ID'sini çıkar
                let videoId = null;
                
                // youtube.com/watch?v=VIDEO_ID
                const match1 = url.match(/[?&]v=([^&]+)/);
                if (match1) videoId = match1[1];
                
                // youtu.be/VIDEO_ID
                const match2 = url.match(/youtu\.be\/([^?]+)/);
                if (match2) videoId = match2[1];

                if (videoId) {
                    // Iframe oluştur
                    const iframe = document.createElement('iframe');
                    iframe.src = `https://www.youtube.com/embed/${videoId}`;
                    iframe.width = '560';
                    iframe.height = '315';
                    iframe.frameBorder = '0';
                    iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                    iframe.allowFullscreen = true;
                    iframe.style.maxWidth = '100%';
                    iframe.style.marginTop = '10px';
                    iframe.style.marginBottom = '10px';

                    // Link'in parent'ını bul ve iframe'i ekle
                    const parent = link.parentElement;
                    if (parent) {
                        parent.appendChild(iframe);
                        link.classList.add('media-processed');
                        link.style.display = 'none'; // Link'i gizle
                    }
                }
            });
        }, 100);
    }

    content() {
        if (this.loading) {
            return <LoadingIndicator />;
        }

        if (this.permissionDenied) {
            return (
                <div className="CustomProfilePage">
                    <div className="Placeholder">
                        <p>{app.translator.trans('huseyinfiliz-custom-profile-page.forum.errors.permission_denied')}</p>
                    </div>
                </div>
            );
        }

        if (this.error) {
            return (
                <div className="CustomProfilePage">
                    <div className="Placeholder">
                        <p>{this.error}</p>
                    </div>
                </div>
            );
        }

        const currentUser = app.session.user;
        
        if (!currentUser) {
            const contentHtml = this.customPage?.attribute('contentHtml') || '';
            
            return (
                <div className="CustomProfilePage">
                    <div className="CustomProfilePage-content Post-body">
                        {contentHtml ? m.trust(contentHtml) : this.renderEmpty(false)}
                    </div>
                </div>
            );
        }

        const isModerator = app.forum.attribute('canModerateCustomPage');
        const isOwnPage = currentUser.id() === this.user?.id();
        const canEditOwn = app.forum.attribute('canEditOwnCustomPage');
        
        const canEdit = isModerator || (isOwnPage && canEditOwn);

        const contentHtml = this.customPage?.attribute('contentHtml') || '';

        return (
            <div className="CustomProfilePage">
                <div className="CustomProfilePage-header">
                    {canEdit && (
                        <Button
                            className="Button Button--primary"
                            icon="fas fa-edit"
                            onclick={() => {
                                app.modal.show(EditCustomPageModal, {
                                    user: this.user,
                                    customPage: this.customPage,
                                    onSave: (page) => {
                                        this.customPage = page;
                                        m.redraw();
                                    }
                                });
                            }}
                        >
                            {app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.edit_button')}
                        </Button>
                    )}
                </div>
                <div className="CustomProfilePage-content Post-body">
                    {contentHtml ? m.trust(contentHtml) : this.renderEmpty(canEdit)}
                </div>
            </div>
        );
    }

    loadCustomPage() {
        if (!this.user) {
            setTimeout(() => this.loadCustomPage(), 100);
            return;
        }

        this.loading = true;
        this.error = null;
        this.permissionDenied = false;
        this.customPage = null;

        app.request({
            method: 'GET',
            url: app.forum.attribute('apiUrl') + '/users/' + this.user.id() + '/custom-page',
        })
        .then((response) => {
            if (response.data) {
                this.customPage = app.store.pushPayload(response);
            }
        })
        .catch((error) => {
            if (error.status === 403) {
                this.permissionDenied = true;
            } else if (error.status === 404) {
                this.customPage = null;
            } else {
                this.error = app.translator.trans('huseyinfiliz-custom-profile-page.forum.errors.load_failed');
            }
        })
        .finally(() => {
            this.loading = false;
            m.redraw();
        });
    }

    renderEmpty(canEdit) {
        return (
            <div className="Placeholder">
                <p>
                    {canEdit 
                        ? app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.empty_own')
                        : app.translator.trans('huseyinfiliz-custom-profile-page.forum.profile_tab.empty_other')
                    }
                </p>
            </div>
        );
    }
}