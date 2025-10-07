import app from 'flarum/forum/app';
import CustomProfilePage from './models/CustomProfilePage';
import addUserProfilePage from './addUserProfilePage';

app.initializers.add('huseyinfiliz/custom-profile-page', () => {
  // Model kaydet
  app.store.models['custom-profile-pages'] = CustomProfilePage;

  // Route ve navigation ekle
  addUserProfilePage();
});