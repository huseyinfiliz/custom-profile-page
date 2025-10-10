import app from 'flarum/forum/app';
import CustomProfilePage from './models/CustomProfilePage';
import addUserProfilePage from './addUserProfilePage';

app.initializers.add('huseyinfiliz/custom-profile-page', () => {
  console.log('[huseyinfiliz/custom-profile-page] Custom Profile Page extension enabled');
  
  // Model'i kaydet
  app.store.models['custom-profile-pages'] = CustomProfilePage;

  // User profile page ekle
  addUserProfilePage();
});