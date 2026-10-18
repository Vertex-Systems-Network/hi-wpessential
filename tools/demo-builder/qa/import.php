<?php
require $argv[1].'/wp-load.php';
wp_set_current_user(1);
$r = WPEssential\Core\Importer::start($argv[2]??'base',$argv[3]??'gutenberg',['reset'=>true]);
if (is_wp_error($r)) { echo $r->get_error_message(); exit; }
$since=0;
for($i=0;$i<200;$i++){ $j=WPEssential\Core\Importer::run($since); foreach($j['log'] as $l) echo str_pad($l['level'],8),$l['step'],': ',$l['msg'],($l['hint']?' -> '.$l['hint']:''),"\n"; $since=$j['seq']; if($j['status']!=='running'){echo "STATUS ",$j['status']," ",$j['progress'],"% ",$j['elapsed'],"s\n";break;} }
