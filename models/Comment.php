<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "comments".
 *
 * @property integer $id
 * @property string $text
 * @property integer $left_key
 * @property integer $right_key
 * @property integer $depth
 * @property integer $parent_id
 * @property string $created
 * @property string $updated
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'comments';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'left_key', 'right_key', 'depth', 'parent_id'], 'required'],
            [['text'], 'string'],
            [['text'],'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['left_key', 'right_key', 'depth'], 'integer'],
            [['created', 'updated'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Text',
            'left_key' => 'Left Key',
            'right_key' => 'Right Key',
            'depth' => 'Depth',
            'parent_id' => 'Parent',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Return max right key value in tree
     *
     * @return int Max value right key
     */
    public static function getMaxRightKey()
    {
        $max = static::find()
            ->select("max(right_key)")
            ->scalar();

        return $max ? $max : 0;
    }

    /**
     * Return nodes by left and right key
     *
     * @param $leftKey
     * @param $rightKey
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getNodes($leftKey, $rightKey)
    {
        return static::find()
            ->select("id, text, depth, created, parent_id")
            ->from('comments')
            ->where(['>', 'left_key', $leftKey])
            ->andWhere(['<', 'right_key', $rightKey])
            ->orderBy('left_key')
            ->all();
    }

    /**
     * Update keys behind parent node
     *
     * @param int $rightKey
     * @throws \yii\db\Exception
     */
    public static function updateBehindParentNode($rightKey)
    {
        \Yii::$app->db->createCommand("UPDATE comments SET left_key = left_key + 2, right_key = right_key + 2, updated = NOW() WHERE left_key > :right_key")
            ->bindValue(':right_key', $rightKey)
            ->execute();
    }

    /**
     * Update parent branch
     *
     * @param int $rightKey
     * @throws \yii\db\Exception
     */
    public static function updateParentBranch($rightKey)
    {
        \Yii::$app->db->createCommand("UPDATE comments SET right_key = right_key + 2, updated = NOW() WHERE right_key >= :right_key AND left_key < :right_key")
            ->bindValue(':right_key', $rightKey)
            ->execute();
    }

    /**
     * Delete node
     *
     * @param $leftKey
     * @param $rightKey
     * @throws \yii\db\Exception
     */
    public static function deleteNode($leftKey, $rightKey)
    {
        // 1. Delete node and sub-nodes
        \Yii::$app->db->createCommand("DELETE FROM comments WHERE left_key >= :left_key AND right_key <= :right_key")
            ->bindValue(':left_key', $leftKey)
            ->bindValue(':right_key', $rightKey)
            ->execute();

        // 2. Update nodes after right key
        \Yii::$app->db->createCommand("UPDATE comments SET left_key = IF(left_key > :left_key, left_key - (:right_key - :left_key + 1), left_key), right_key = right_key - (:right_key - :left_key + 1), updated = NOW() WHERE right_key > :right_key")
            ->bindValue(':left_key', $leftKey)
            ->bindValue(':right_key', $rightKey)
            ->execute();
    }
}
