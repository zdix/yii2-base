<?php

namespace dix\base\web\model;

use dix\base\component\DXUtil;
use dix\base\component\ModelApiInterface;
use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $uid
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $phone
 * @property string $name
 * @property integer $gender
 * @property integer $birthday
 * @property string $avatar
 * @property integer $source_id
 * @property integer $source_type
 * @property integer $source_client
 * @property integer $weight
 * @property integer $create_time
 * @property integer $update_time
 */
class User extends \yii\db\ActiveRecord implements ModelApiInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid'], 'required'],
            [['phone', 'uid'], 'unique'],
            [['gender', 'birthday', 'source_id', 'source_type', 'source_client', 'weight', 'create_time', 'update_time'], 'integer'],
            [['uid', 'username', 'password', 'email', 'phone', 'name', 'avatar'], 'string', 'max' => 99]
        ];
    }

    public static function basicAttributes()
    {
        return array_keys([
            'id' => 'ID',
            'uid' => 'Uid',
            'username' => 'Username',
            'phone' => 'Phone',
            'name' => 'Name',
            'avatar' => 'Avatar',
        ]);
    }

    public static function strictAttributes()
    {
        return array_keys([
            'id' => 'ID',
            'uid' => 'Uid',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone',
            'name' => 'Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'avatar' => 'Avatar',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ]);
    }

    public static function detailAttributes()
    {
        return array_keys([
            'id' => 'ID',
            'uid' => 'Uid',
            'username' => 'Username',
            'email' => 'Email',
            'phone' => 'Phone',
            'name' => 'Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'avatar' => 'Avatar',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'username' => 'Username',
            'password' => 'Password',
            'email' => 'Email',
            'phone' => 'Phone',
            'name' => 'Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'avatar' => 'Avatar',
            'source_id' => 'Source ID',
            'source_type' => 'Source Type',
            'source_client' => 'Source Client',
            'weight' => 'Weight',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public static function attributeTypes()
    {
        $types = [
            'id' => 'i',
            'uid' => 's',
            'username' => 's',
            'password' => 's',
            'email' => 's',
            'phone' => 's',
            'name' => 's',
            'gender' => 'i',
            'birthday' => 'i',
            'avatar' => 's',
            'source_id' => '',
            'source_type' => 'i',
            'source_client' => 'i',
            'weight' => 'i',
            'create_time' => 'i',
            'update_time' => 'i',
        ];

        return $types;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert))
        {

            $this->update_time = time();
            if ($insert)
            {
                $this->create_time = $this->update_time;
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function processRaw($model, $keys = null)
    {
        $keys = $keys ?: self::basicAttributes();
        $types = self::attributeTypes();
        $model = DXUtil::processModel($model, $keys, $types);

        return $model;
    }

    public static function processRawDetail($model)
    {
        $model = DXUtil::processModel($model, self::detailAttributes(), self::attributeTypes());

        return $model;
    }

    /**
     * @param $id
     * @return null|\smartwork\user\api\model\User
     */
    public static function findById($id)
    {
        return self::find()->where('weight >= 0')->andWhere(' id = :id ', [':id' => intval($id)])->one();
    }

    /**
     * @param $uid
     * @return null|\smartwork\user\api\model\User
     */
    public static function findByUid($uid)
    {
        return self::find()->where('weight >= 0')->andWhere(' LOWER(uid) = :uid ', [':uid' => strtolower($uid)])->one();
    }

    /**
     * @param $phone
     * @return null|\smartwork\user\api\model\User
     */
    public static function findByPhone($phone)
    {
        $phone = '' . intval($phone);
        return self::find()->where('weight >= 0')->andWhere(['phone' => $phone])->one();
    }

    public static function getUserRawById($id)
    {
        $user = self::findById($id);
        $user = self::processRaw($user);
        return $user;
    }

    public static function exists($user_id)
    {
        return self::find()->where(' weight >= 0 ')->andWhere(['id' => intval($user_id)])->exists();
    }

    public static function getUserRawListByIdList($user_id_list)
    {
        $user_list = [];

        if (is_array($user_id_list) && !empty($user_id_list))
        {
            $user_id_list_checked = [];
            foreach ($user_id_list as $user_id)
            {
                $user_id = intval($user_id);
                if ($user_id > 0 && !in_array($user_id, $user_id_list_checked))
                {
                    $user_id_list_checked[] = $user_id;
                }
            }

            $db_user_list = self::find()->where(' weight >= 0 ')->andWhere(['in', 'id', $user_id_list_checked])->asArray()->all();
            foreach ($db_user_list as $db_user)
            {
                $user = User::processRaw($db_user);
                if ($user)
                {
                    $user_list[] = $user;
                }
            }
        }

        return $user_list;
    }

    public static function validateMultiUserIdString($user_id_str)
    {
        $user_id_str_checked = '';
        if ($user_id_str)
        {

            $user_id_str = trim($user_id_str);
            $user_id_str = trim($user_id_str, ',');
            $user_id_list = explode(',', $user_id_str);
            $user_id_list_checked = [];
            foreach ($user_id_list as $user_id)
            {
                if ($user_id && User::exists($user_id))
                {
                    $user_id_list_checked[] = $user_id;
                }
            }
            $user_id_str_checked = implode(',', $user_id_list_checked);
        }

        return $user_id_str_checked;
    }

}
