<?php
defined ( 'IN_TS' ) or die ( 'Access Denied.' );
// 用户是否登录
$userid = aac ( 'user' )->isLogin ();

switch ($ts) {

    case "do" :

        $articleid = intval ( $_POST ['articleid'] );
        $content = tsClean ( $_POST ['content'] );
        $content2 = emptyText( $_POST ['content'] );

        if ($content2 == '') tsNotice ( "内容不能为空！" );

        // 过滤内容开始
        aac ( 'system' )->antiWord ( $content );
        // 过滤内容结束

        $new ['article']->create ( 'article_comment', array (
            'articleid' => $articleid,
            'userid' => $userid,
            'content' => $content,
            'addtime' => time ()
            )
        );

        // 统计
        $count_comment = $new ['article']->findCount ( 'article_comment', array (
            'articleid' => $articleid
        ) );

        $new ['article']->update ( 'article', array (
            'articleid' => $articleid
        ), array (
            'count_comment' => $count_comment
        ) );


        $daytime = strtotime(date('Y-m-d 00:00:01'));
        $count_comment = $new['article']->findCount('article_comment',"`userid`='$userid' and `addtime`>'$daytime'");

        #每日前1条给积分
        if($count_comment<2){
            aac('user') -> doScore($GLOBALS['TS_URL']['app'], $GLOBALS['TS_URL']['ac'], $GLOBALS['TS_URL']['ts']);
        }


        header ( "Location: " . tsUrl ( 'article', 'show', array ('id' => $articleid) ) );

        break;

    case "delete" :

        $commentid = intval ( $_GET ['commentid'] );

        $strComment = $new ['article']->find ( 'article_comment', array (
            'commentid' => $commentid
        ) );

        $strArticle = $new ['article']->find ( 'article', array (
            'articleid' => $strComment ['articleid']
        ) );

        if ($userid == $strArticle ['userid'] || $TS_USER ['isadmin'] == 1) {

            $new ['article']->delete ( 'article_comment', array (
                'commentid' => $commentid
            ));

            //处理积分
            aac('user')->doScore($GLOBALS['TS_URL']['app'], $GLOBALS['TS_URL']['ac'], $GLOBALS['TS_URL']['ts'],$strComment['userid']);

            tsNotice ( '删除成功' );
        } else {

            tsNotice ( '非法操作' );
        }

        break;
}