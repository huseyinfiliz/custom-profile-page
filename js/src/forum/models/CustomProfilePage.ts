import Model from 'flarum/common/Model';
import type User from 'flarum/common/models/User';

export default class CustomProfilePage extends Model {
  content = Model.attribute<string>('content');
  contentHtml = Model.attribute<string>('contentHtml');
  userId = Model.attribute<number>('userId');
  createdAt = Model.attribute('createdAt', Model.transformDate);
  updatedAt = Model.attribute('updatedAt', Model.transformDate);

  user = Model.hasOne<User>('user');
}
