import Model from 'flarum/common/Model';

export default class CustomProfilePage extends Model {
  content = Model.attribute<string>('content');
  updatedAt = Model.attribute('updatedAt', Model.transformDate);
  
  user = Model.hasOne('user');
}