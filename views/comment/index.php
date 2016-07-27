<?php
use yii\helpers\Html;
/* @var $this yii\web\View */

$this->title = 'Comments tree';
?>
<h1><?= Html::encode($this->title) ?></h1>
<ul id="main_level">
    <li id="comment_0">
        <ul id="comment_ul_0">
<?php
    /** @var Object[] $comments */
    foreach ($comments as $comment) {
        ?>
            <li id="comment_<?=$comment->id;?>">
                <div class="button" id="comment_btn_<?=$comment->id?>">+</div>
                <span class="node"><span class="text" id="comment_text_<?=$comment->id;?>"><?=$comment->text?></span>
                    <a href="#" class="comment_add">Add comment</a>
                    <a href="#" class="comment_del">Delete</a>
                    <a href="#" class="comment_edit">Edit</a>
                </span>
            </li>
        <?php
    } ?>
        </ul>
        <span class="node first_node"><a href="#" class="comment_add first_comment" >Add main level comment</a></span>
    </li>
</ul>
