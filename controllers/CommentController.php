<?php

namespace app\controllers;

use app\models\Comment;
use Yii;
use yii\web\NotFoundHttpException;

class CommentController extends ActiveController
{
    /**
     * @var string Layout name
     */
    public $layout = 'comment';

    /**
     * Displays comments list
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex()
    {
        $model = new Comment;

        $comments = Comment::find()
            ->where(['depth' => 1])
            ->all();

        return $this->render('index', [
            'model' => $model,
            'comments' => $comments,
        ]);
    }

    /**
     * Get list of comments by parent id
     *
     * @param int $id Parent comment id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionList($id)
    {
        /** @var Comment $parentComment */
        $parentComment = Comment::findOne(['id' => $id]);

        if (is_null($parentComment)) {
            return [
                'success' => false,
                'error' => 'Parent comment not found',
            ];
        }

        // get children nodes
        $items = Comment::getNodes($parentComment->left_key, $parentComment->right_key);

        return [
            'success' => true,
            'data' => $items
        ];
    }

    /**
     * Delete comment and sub-tree
     *
     * @param int $id Comment id
     * @return array Response
     */
    public function actionCreate($id)
    {
        $data = $this->getRawData();

        $comment = new Comment();
        $comment->text = $data['text'];

        // if main-level comment
        if ($id == 0) {
            $depth = 0;
            $rightKey = Comment::getMaxRightKey() + 1;
        } else {
            // else if not main-level, find parent
            /** @var Comment $parentComment */
            $parentComment = Comment::findOne(['id' => $id]);

            if (is_null($parentComment)) {
                return [
                    'success' => false,
                    'error' => 'Parent comment not found',
                ];
            }

            // get depth and right key
            $depth = $parentComment->depth;
            $rightKey = $parentComment->right_key;

            // 1. Update keys behind parent node
            Comment::updateBehindParentNode($rightKey);
        }

        // 2. Update parent branch
        Comment::updateParentBranch($rightKey);

        // set new attributes to node
        $comment->left_key = $rightKey;
        $comment->right_key = $rightKey + 1;
        $comment->depth = $depth + 1;
        $comment->parent_id = $id;

        // save new comment
        $comment->save();
        $comment->refresh();

        return [
            'success' => true,
            'data' => [
                'id' => $comment->id,
                'text' => $comment->text,
            ],
        ];
    }

    /**
     * Update comment
     *
     * @param int $id Comment id
     * @return array Response
     */
    public function actionUpdate($id)
    {
        /** @var Comment $comment */
        $comment = Comment::findOne(['id' => $id]);
        if (is_null($comment)) {
            return [
                'success' => false,
                'error' => 'Comment not found',
            ];
        }

        $data = $this->getRawData();

        $comment->text = $data['text'];

        // save updated comment
        $comment->save();
        return [
            'success' => true,
            'data' => $comment,
        ];
    }

    /**
     * Delete comment and sub-tree
     *
     * @param int $id Comment id
     * @return array Response
     */
    public function actionDelete($id)
    {
        /** @var Comment $comment */
        $comment = Comment::findOne(['id' => $id]);
        if (is_null($comment)) {
            return [
                'success' => false,
                'error' => 'Comment not found',
            ];
        }

        Comment::deleteNode($comment->left_key, $comment->right_key);

        return [
            'success' => true
        ];
    }
}
