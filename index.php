<?php
require_once 'bootstrap.php';
require 'splroutes/sitemap.php';

// Home page
$app->get('/', function($msg = "") use ($app, $template_arr) {
	
	$template_arr['action_name'] = 'Write';
	$template_arr['action_url'] = BASE_URL;
	$template_arr['recaptcha_html'] = recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
	
	return $app->render('home.html.twig', $template_arr);
});

// Home page post
$app->post('/', function() use ($app, $template_arr) {
	
	$from = $app->request()->post('from');
	$to = $app->request()->post('to');
	$content = $app->request()->post('content');
	$comments = intval($app->request()->post('comments'));
	
	$recaptcha_resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
											$_SERVER["REMOTE_ADDR"],
											$app->request()->post('recaptcha_challenge_field'),
											$app->request()->post('recaptcha_response_field')
											);
	
	if(!empty($from) and $from != "Your Name" and !empty($to) and $to != "Name of the person you're addressing to" and !empty($content) and $recaptcha_resp->is_valid)
	{
		$letter = Model::factory('Letter')->create();
		$letter->from = $from;
		$letter->to = $to;
		$letter->content = $content;
		$letter->comments = $comments;
		$letter->timestamp = date('Y-m-d H:i:s');
		$letter->ip = $_SERVER['REMOTE_ADDR'];
		
		$purifier = new HTMLPurifier();
		$letter->content = $purifier->purify($letter->content);
		
		$slug = new slugger();
		$letter->from_slug = $slug->sluggify($letter->from);
		$letter->to_slug = $slug->sluggify($letter->to);
		
		if($letter->from_slug == "")
			$letter->from_slug = $slug->randomize();
		
		if($letter->to_slug == "")
			$letter->to_slug = $slug->randomize();
		
		$letter->edit_slug = hash('sha256', "/from/".$letter->from_slug."/to/".$letter->to_slug.md5(time()));
		$letter->save();
		$app->redirect(BASE_URL.'edit/'.$letter->edit_slug);
	}
	else
	{
		$template_arr['recaptcha_html'] = recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
		$template_arr['action_name'] = 'Write';
		$template_arr['action_url'] = BASE_URL;
		$template_arr['errors'] = '';
		$template_arr['posted_from'] = $from;
		$template_arr['posted_to'] = $to;
		$template_arr['posted_content'] = $content;
		$template_arr['posted_comments'] = $comments;
		
		if(empty($from) || $from == "Your Name")
			$template_arr['errors'] .= '<br>Please mention to whom you are writing this letter!';
		if(empty($to) || $to == "Name of the person you're addressing to")
			$template_arr['errors'] .= '<br>Please mention who you are! ;)';
		if(empty($content))	
			$template_arr['errors'] .= '<br>Please write at least something in your letter!';
		if(!$recaptcha_resp->is_valid)
			$template_arr['errors'] .= '<br>The captcha entered was wrong. Please enter the correct captcha!';
		
		return $app->render('home.html.twig', $template_arr);
	}
});

// Latest
$app->get('/latest(/:page)', function($page = 1) use ($app, $template_arr) {
	$count = Model::factory('Letter')->count();
	$paginate = new pagination($count, $page, BASE_URL.'latest/');	
	$sqlparams = $paginate->paginate();
	$letters = Model::factory('Letter')->limit($sqlparams['rows_per_page'])->offset($sqlparams['offset'])->order_by_desc('timestamp')->find_many();
	$template_arr['letters'] = $letters;
	$template_arr['pagination'] = $paginate->renderFullNav();
	return $app->render('latest.html.twig', $template_arr);
});

// Explore
$app->get('/explore', function() use ($app, $template_arr) {
	$letter = ORM::for_table('Letter')->raw_query('SELECT * FROM letter WHERE RAND()<(SELECT ((1/COUNT(*))*10) FROM letter) ORDER BY RAND() LIMIT 1;', array())->find_one();
	$template_arr['letter'] = $letter;
	return $app->render('letter.html.twig', $template_arr);
});

$app->get('/tos', function() use ($app, $template_arr) {
	return $app->render('tos.html.twig', $template_arr);
});

$app->get('/about', function() use ($app, $template_arr) {
	return $app->render('about.html.twig', $template_arr);
});

//Edit Get
$app->get('/edit/(:edit_slug)', function($edit_slug) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->where('edit_slug', $edit_slug)->find_one();
	$template_arr['action_name'] = 'Edit';
	$template_arr['action_url'] = BASE_URL.'edit/'.$edit_slug;
	$template_arr['edit_slug'] = $edit_slug;
	$template_arr['letter'] = $letter;
	return $app->render('edit.html.twig', $template_arr);
});

//Edit Post
$app->post('/edit/(:edit_slug)', function($edit_slug) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->where('edit_slug', $edit_slug)->find_one();
	if (! $letter instanceof Letter) {
	   $app->notFound();
	}
	
	$from = $app->request()->post('from');
	$to = $app->request()->post('to');
	$content = $app->request()->post('content');
	$comments = intval($app->request()->post('comments'));
	
	if(!empty($from) and !empty($to) and !empty($content))
	{
		$letter->from = $from;
		$letter->to = $to;
		$letter->content = $content;
		$letter->comments = $comments;
		$letter->ip = $_SERVER['REMOTE_ADDR'];
		$letter->timestamp = date('Y-m-d H:i:s');
		
		$purifier = new HTMLPurifier();
		$letter->content = $purifier->purify($letter->content);
		
		$slug = new slugger();
		$letter->from_slug = $slug->sluggify($letter->from);
		$letter->to_slug = $slug->sluggify($letter->to);

		$letter->save();
		$app->redirect(BASE_URL.'edit/'.$edit_slug);
	}
	else
	{
		$template_arr['action_name'] = 'Update';
		$template_arr['action_url'] = BASE_URL.'edit/'.$edit_slug;
		$template_arr['errors'] = '';
		
		if(empty($from))
			$template_arr['errors'] .= '<br>Please mention to whom you are writing this letter!';
		if(empty($to))
			$template_arr['errors'] .= '<br>Please mention who you are! ;)';
		if(empty($content))	
			$template_arr['errors'] .= '<br>Please write at least something in your letter!';
		
		$template_arr['letter'] = $letter;
		
		return $app->render('edit.html.twig', $template_arr);
	}
});

// View Open Letter
$app->get('/(:id)/from/(:from_slug)/to/(:to_slug)', function($id, $from_slug, $to_slug) use ($app, $template_arr) {
	if(empty($from_slug) || empty($to_slug) || empty($id))
		$app->notFound();
	$letter = Model::factory('Letter')->find_one($id);
	if (!$letter instanceof Letter) {
	  $app->notFound();
	}
	$template_arr['letter'] = $letter;
	return $app->render('letter.html.twig', $template_arr);
});

// Ask for confirmation to delete letter?
$app->get('/delete/(:edit_slug)', function($edit_slug) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->where('edit_slug', $edit_slug)->find_one();
	if (! $letter instanceof Letter) {
	   $app->notFound();
	}
	$template_arr['letter'] = $letter;
	$template_arr['action_url'] = BASE_URL.'delete/'.$edit_slug;
	return $app->render('delete.html.twig', $template_arr);
});

// Delete letter if yes or redirect to edit page if not
$app->post('/delete/(:edit_slug)', function($edit_slug) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->where('edit_slug', $edit_slug)->find_one();
	if (! $letter instanceof Letter) {
	   $app->notFound();
	}
	if($app->request()->post('action') == "Yes")
	{
		$letter->delete();
		$app->redirect(BASE_URL);
	}
	else
	{
		$app->redirect(BASE_URL.'edit/'.$edit_slug);
	}
});

// Admin Home.
$app->get('/'.$adminPath, function() use ($app, $template_arr) {
	$letters = Model::factory('Letter')->order_by_desc('timestamp')->find_many();
	$template_arr['letters'] = $letters;
	return $app->render('admin_home.html.twig', $template_arr);
});

// Admin Add.
$app->get('/'.$adminPath.'/add', function() use ($app, $template_arr) {
	$template_arr['action_name'] = 'Add';
	$template_arr['action_url'] = BASE_URL.$template_arr['admin_path'].'/add';
	return $app->render('admin_input.html.twig', $template_arr);
});
 
// Admin Add - POST.
$app->post('/'.$adminPath.'/add', function() use ($app, $template_arr) {
	$from = $app->request()->post('from');
	$to = $app->request()->post('to');
	$content = $app->request()->post('content');
	$comments = intval($app->request()->post('comments'));
	
	if(!empty($from) and !empty($to) and !empty($content))	
	{
		$letter = Model::factory('Letter')->create();
		$letter->from = $app->request()->post('from');
		$letter->to = $app->request()->post('to');
		$letter->content = $app->request()->post('content');
		$letter->comments = intval($app->request()->post('comments'));
		$letter->timestamp = date('Y-m-d H:i:s');
		$letter->ip = $_SERVER['REMOTE_ADDR'];
		
		$purifier = new HTMLPurifier();
		$letter->from = $letter->from;
		$letter->to = $letter->to;
		$letter->content = $purifier->purify($letter->content);
		
		$slug = new slugger();
		$letter->from_slug = $slug->sluggify($letter->from);
		$letter->to_slug = $slug->sluggify($letter->to);
		
		$letter->edit_slug = hash('sha256', "/from/".$letter->from_slug."/to/".$letter->to_slug.md5(time()));
		$letter->save();
		$app->redirect(BASE_URL.$template_arr['admin_path']);
	}
	else
	{
		$template_arr['action_name'] = 'Add';
		$template_arr['action_url'] = BASE_URL.$template_arr['admin_path'].'/add';
		$template_arr['errors'] = '';
		$template_arr['posted_from'] = $from;
		$template_arr['posted_to'] = $to;
		$template_arr['posted_content'] = $content;
		$template_arr['posted_comments'] = $comments;
		
		if(empty($from))
			$template_arr['errors'] .= '<br>Please mention to whom you are writing this letter!';
		if(empty($to))
			$template_arr['errors'] .= '<br>Please mention who you are! ;)';
		if(empty($content))	
			$template_arr['errors'] .= '<br>Please write at least something in your letter!';
		
		return $app->render('admin_input.html.twig', $template_arr);
	}
});
 
// Admin Edit.
$app->get('/'.$adminPath.'/edit/(:id)', function($id) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->find_one($id);
	if (! $letter instanceof Letter) {
		$app->notFound();
	}
	$template_arr['action_name'] = 'Edit';
	$template_arr['action_url'] = BASE_URL.$template_arr['admin_path'].'/edit/' . $id;
	$template_arr['letter'] = $letter;
	return $app->render('admin_input.html.twig', $template_arr);
});
 
// Admin Edit - POST.
$app->post('/'.$adminPath.'/edit/(:id)', function($id) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->find_one($id);
	if (! $letter instanceof Letter) {
	   $app->notFound();
	}
	$from = $app->request()->post('from');
	$to = $app->request()->post('to');
	$content = $app->request()->post('content');
	$comments = intval($app->request()->post('comments'));
	
	if(!empty($from) and !empty($to) and !empty($content))
	{
		error_log('here letter = '.$from);
		$letter->from = $from;
		$letter->to = $to;
		$letter->content = $content;
		$letter->comments = $comments;
		$letter->ip = $_SERVER['REMOTE_ADDR'];
		$letter->timestamp = date('Y-m-d H:i:s');
		$purifier = new HTMLPurifier();
		$letter->content = $purifier->purify($letter->content);
		
		$slug = new slugger();
		$letter->from_slug = $slug->sluggify($letter->from);
		$letter->to_slug = $slug->sluggify($letter->to);
		$letter->save();
		$app->redirect(BASE_URL.$template_arr['admin_path']);
		
	}
	else
	{
		$template_arr['action_name'] = 'Edit';
		$template_arr['action_url'] = BASE_URL.$template_arr['admin_path'].'/edit/' . $id;
		$template_arr['errors'] = '';
		
		if(empty($from))
			$template_arr['errors'] .= '<br>Please mention to whom you are writing this letter!';
		if(empty($to))
			$template_arr['errors'] .= '<br>Please mention who you are! ;)';
		if(empty($content))	
			$template_arr['errors'] .= '<br>Please write at least something in your letter!';
			
		$template_arr['letter'] = $letter;
		
		return $app->render('admin_input.html.twig', $template_arr);
	}
});
 
// Admin Delete.
$app->get('/'.$adminPath.'/delete/(:id)', function($id) use ($app, $template_arr) {
	$letter = Model::factory('Letter')->find_one($id);
	if ($letter instanceof Letter) {
	   $letter->delete();
	}
	$app->redirect(BASE_URL.$template_arr['admin_path']);
});

$app->run();
?>
