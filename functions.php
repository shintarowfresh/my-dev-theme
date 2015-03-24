<?php
/**
 * functions
 *
 * テーマでは、functions.phpという名前の関数ファイルを使用することができます
 * 基本的にプラグインのように動作し、テーマに存在していれば自動的にWordPressの初期化時に読み込まれます
 *
 */
if ( ! function_exists( 'comman_setup' ) ):
    /**
     * テーマのデフォルト設定や、WordPress 諸機能のサポートを登録・設定します。
     *
     * この関数は init フックの前に実行される after_setup_theme フックへ繋がっていることに注意してください。
     * init のアクションフックだと間に合わない機能があるからです。
     */
    function comman_setup() {

    //自動的にRSSフィードのリンクを挿入
    add_theme_support( 'automatic-feed-links' );

    //アイキャッチ有効化
    add_theme_support( 'post-thumbnails');

    // カスタム背景有効化
    add_theme_support( 'custom-background' );

    // コメントフォーム、検索フォーム、コメントリストを html5 マークアップにしてくれる
    add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form' ) );

    // ビジュアルエディタ用の css 読み込み
    add_editor_style( 'style.css' );

    // メニューを登録
    register_nav_menu( 'primary', 'ヘッダーのメニュー' );
    register_nav_menu( 'init-cat', 'カテゴリーリストの中のメニュー' );

    }

endif;
// 'after_setup_theme' フックが実行された時に 'comman_setup' 関数を実行する処理
add_action( 'after_setup_theme', 'comman_setup' );


/**
 * スクリプトとスタイルのエンキュー、アクションフック
 */
function comman_scripts() {

    // メインのスタイルシート
    wp_enqueue_style( 'body-style', get_stylesheet_directory_uri() . '/style.css', array() ,null );
    wp_enqueue_style( 'swipeshow-style', get_stylesheet_directory_uri() . '/css/jquery.swipeshow.min.css', array() ,null );
    wp_enqueue_style( 'main-style', get_stylesheet_directory_uri() . '/css/build.css', array() ,null  );
    wp_enqueue_style( 'fa-anime-style', get_stylesheet_directory_uri() . '/css/font-awesome-animation.min.css', array() ,null );
    wp_enqueue_style( 'icon-style', get_stylesheet_directory_uri() . '/css/icomoon/style.css', array() ,null );
    wp_enqueue_style( 'fa-style', 'http://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array() ,null );
    wp_enqueue_style( 'gf-style', 'http://fonts.googleapis.com/css?family=Roboto:100,700,400', array() ,null );

    // コメント用スクリプト
    if ( is_singular() )
        wp_enqueue_script( 'comment-reply' );


    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-1.11.2.min.js', array(), null);

    // メインの js
    wp_enqueue_script( 'script-js', get_template_directory_uri() . '/js/script.min.js', array() ,null, true);
    wp_enqueue_script( 'main-js', get_template_directory_uri() . '/js/main.js', array() ,null ,true );


    // コンソールエラー回避のためのヘルパースクリプト
    if ( WP_DEBUG )
        wp_enqueue_script( 'plugins-js', get_template_directory_uri() . '/js/plugins.js' );
}
add_action( 'wp_enqueue_scripts', 'comman_scripts' );


/**
 * メインのサイドバーを定義
 *
 * @since WordPress 2.2 (2.9.0: description プロパティ追加
 */
register_sidebar( $args = array(
        // サイドバーの名前、2つめの引数でテキストドメインを指定
        'name'          => __( 'メインのサイドバー', 'テーマのテキストドメイン' ),
        // サイドバー呼び出し用のID。小文字かつスペースは無きよう。
        'id'            => 'sidebar-main',
        // サイドバーの説明
        'description'   => '',
        // サイドバーウィジェットに付加されるクラス
        'class'         => '',
        // ウィジェットの前に配置する HTML
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        // ウィジェットの後に配置する HTML
        'after_widget'  => '</aside>',
        // ウィジェットタイトルの前に配置する HTML
        'before_title'  => '<h3 class="widgettitle">',
        // ウィジェットタイトルの後に配置する HTML
        'after_title'   => '</h3>' )
);

/**
* コンテンツエリアの最大許容幅を設定
*
*/
if ( ! isset( $content_width ) )
    $content_width = 800;

//サムネ画像のサイズ制御
set_post_thumbnail_size( 150, 150, true );
//アップロード時に自動生成する画像のサイズを制御
add_image_size( 'single-eye', 763, 400, true );

/**
*グローバルメニューのサブタイトル化

*
*/
add_filter('walker_nav_menu_start_el', 'description_in_nav_menu', 10, 4);

function description_in_nav_menu($item_output, $item){
    return preg_replace('/(<a.*?>[^<]*?)</', '$1' . "<span>{$item->attr_title}</span><", $item_output);
}

/**
*各アーカイブの投稿数をコントロール
*
*/
function change_posts_per_page($query) {
    if ( is_admin() || ! $query->is_main_query() )
        return;

    if ( $query->is_post_type_archive('work') ) {
        $query->set( 'posts_per_page', '9' );
    }
    if ( $query->is_tax() ) {
        $query->set( 'posts_per_page', '9' );
    }
    //カテゴリーページの表示件数を5件にする
    if ( $query->is_category() || is_author()  ) {
         $query->set( 'posts_per_page', '6' );
         return;
    }
}
add_action( 'pre_get_posts', 'change_posts_per_page' );


//ページネーションを追加
function pagination($pages = '', $range = 2)
{
     $showitems = ($range * 2)+1;//表示するページ数（５ページを表示）

     global $paged;//現在のページ値
     if(empty($paged)) $paged = 1;//デフォルトのページ

     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;//全ページ数を取得
         if(!$pages)//全ページ数が空の場合は、１とする
         {
             $pages = 1;
         }
     }

     if(1 != $pages)//全ページが１でない場合はページネーションを表示する
     {
         echo "<div class=\"pagenation\">\n";
         echo "<ul>\n";
         //Prev：現在のページ値が１より大きい場合は表示
         if($paged > 1) echo "<li class=\"prev\"><a href='".get_pagenum_link($paged - 1)."'>Prev</a></li>\n";

         for ($i=1; $i <= $pages; $i++)
         {
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
             {
                //三項演算子での条件分岐
                echo ($paged == $i)? "<li class=\"active\">".$i."</li>\n":"<li><a href='".get_pagenum_link($i)."'>".$i."</a></li>\n";
             }
         }
        //Next：総ページ数より現在のページ値が小さい場合は表示
        if ($paged < $pages) echo "<li class=\"next\"><a href=\"".get_pagenum_link($paged + 1)."\">Next</a></li>\n";
        echo "</ul>\n";
        echo "</div>\n";
     }
}

//抜粋の文字数制御
function new_excerpt_mblength($length) {
    return 50;
}
add_filter('excerpt_mblength', 'new_excerpt_mblength');

// 文末文字を変更する
function custom_excerpt_more($more) {
    return ' ... ';
}
add_filter('excerpt_more', 'custom_excerpt_more');


//ページの親子関係判別
function is_child( $parent = '' ) {
    global $post;

    $parent_obj = get_page( $post->post_parent, ARRAY_A );
    $parent = (string) $parent;
    $parent_array = (array) $parent;

    if ( in_array( (string) $parent_obj['ID'], $parent_array ) ) {
        return true;
    } elseif ( in_array( (string) $parent_obj['post_title'], $parent_array ) ) {
        return true;
    } elseif ( in_array( (string) $parent_obj['post_name'], $parent_array ) ) {
        return true;
    } else {
        return false;
    }
}


//モバイル条件分岐
function is_mobile() {
    $useragents = array(
        'iPhone',          // iPhone
        'iPod',            // iPod touch
        'Android',         // 1.5+ Android
        'dream',           // Pre 1.5 Android
        'CUPCAKE',         // 1.5+ Android
        'blackberry9500',  // Storm
        'blackberry9530',  // Storm
        'blackberry9520',  // Storm v2
        'blackberry9550',  // Storm v2
        'blackberry9800',  // Torch
        'webOS',           // Palm Pre Experimental
        'incognito',       // Other iPhone browser
        'webmate'          // Other iPhone browser
    );
    $pattern = '/'.implode('|', $useragents).'/i';
    return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
}

//検索結果を操作
function SearchFilter($query) {
    if ( !is_admin() && $query->is_main_query() && $query->is_search() ) {
        $query->set('post_type', 'work');
    }
}
add_action( 'pre_get_posts','SearchFilter' );


//bodyのクラスにページスラッグ
function pagename_class($classes = '') {
    if (is_page()) {
        $page = get_page(get_the_ID());
        $classes[] = $page->post_name;
    }
    return $classes;
}
add_filter('body_class','pagename_class');
add_filter('body_class','pagename_class');


// CSSとJavaScriptのバージョン表記を削除
function remove_cssjs_ver( $src ) {
    if( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'script_loader_src', 'remove_cssjs_ver', 10, 2 );
add_filter( 'style_loader_src', 'remove_cssjs_ver', 10, 2 );

//バリデーションのメッセージ
add_filter('gform_validation_message', 'change_validation_message', 10, 2);
function change_validation_message($message, $form)
{
    return "<div class='validation_error'>ご確認ください。必須項目で未入力やミスがあります。</div>";
}


//関連記事用ショートコード
function articleFunc($atts) {
    extract(shortcode_atts(array(
        'mode' => null,'type' => null,'id' => null,
        'y' => null,'m' => null,'d' => null,
        'numberposts' => 5,'offset' => null,'order' => 'DESC','orderby' => 'post_date','meta_key' => null,
        'include' => null,'exclude' => null,
        'head' => null,'tail' => null,
    ),$atts));

    if($mode != null) $mode = '&'.$mode.'='.$id;
    $post = get_posts('post_status=publish&numberposts='.$numberposts.'&offset='.$offset.'&order='.$order.'&orderby='.$orderby.'&include='.$include.'&year='.$y.'&monthnum='.$m.'&day='.$d.'&exclude='.get_the_ID().','.$exclude.'&meta_key='.$meta_key.$mode);

    foreach ($post as $item){
        $im = wp_get_attachment_image_src(get_post_thumbnail_id($item->ID),'cat-img',true);
        $date = date('Y.m.d',strtotime(get_post($item->ID)->post_date));
        $update = date('Y.m.d',strtotime(get_post($item->ID)->post_modified));
        $echo .= $type=='custom' ? '<div class="mypost"><a href="'.get_permalink($item->ID).'"><img src="'.$im[0].'"><div class="mypost__title"></a>【関連】&nbsp;<a href="'.get_permalink($item->ID).'">'.$item->post_title.'</a></div></div>'
            : $head.'<a href="'.get_permalink($item->ID).'">'.$item->post_title.'</a>'.$tail;
    }

    return $echo;
}
add_shortcode('article','articleFunc');

//jetpuckの機能を止める。
function dequeue_devicepx() {
wp_dequeue_script( 'devicepx' );
}
add_action( 'wp_enqueue_scripts', 'dequeue_devicepx', 20 );

//jetpuckで人気記事
function my_pop_list( $target_days, $n ) {
	$i = 0;
	$args = array( 'days'=>$target_days, 'limit'=>$n+2 );
	$top_posts = stats_get_csv( 'postviews', $args );
	echo "<ol>\n";
	foreach ( $top_posts as $value ) {
		$my_id = $value['post_id']; //投稿ID取得
		if ( $my_id != 0 && $my_id != get_the_ID() && get_post_type($my_id) == 'post' ) { //homeと現在のページは除外で、投稿のみ
			if( has_post_thumbnail($my_id) ) { //サムネイルの有無
				$pop_img = get_the_post_thumbnail( $my_id, thumbnail, array('alt'=>get_the_title($my_id)) );
			} else {
				$pop_img = '<img src="noimage.png" width="100" height="100" />';
			}
			echo '<li><div class="content-thum"><a href="'.$value['post_permalink'].'">'.$pop_img.'</a></div><div class="content-main"><a href="'.$value['post_permalink'].'">'.$value['post_title'].'</a>('.$value['views']."views)</div></li>\n";
			$i++;
			if ( $i >= $n ) { break; } //指定数を超えたら終了
		}	
	}
	echo "</ol>\n";
}