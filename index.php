<?php

require_once 'image.class.php';

if($_POST){
    $type = $_POST['type'];
    
    $image = $_POST['image'];
    $save_image = $_POST['save_image'];
    if($type == 1){
        $quality = $_POST['quality'];
        $is_cut = $_POST['is_cut'];
        $maxWidth = $_POST['maxWidth'];
        $maxHeight = $_POST['maxHeight'];
        
        if(empty($quality))$quality = 8;
        
        $img = new image();
        $result = $img->thumb($image, $save_image,$quality , $is_cut, $maxWidth, $maxHeight);
        
        if($result['code'] == 0){
            $old_img = getimagesize($image);
            $result['old_info']['size'] = round(filesize($image)/1024,1);
            $result['old_info']['width'] = $old_img[0];
            $result['old_info']['height'] = $old_img[1];
            
            $new_img = getimagesize($save_image);
            $result['new_info']['size'] = round(filesize($save_image)/1024,1);
            $result['new_info']['width'] = $new_img[0];
            $result['new_info']['height'] = $new_img[1];
        }
        
        echo json_encode($result);
        exit;
    }elseif ($type == 2){
        $markType = $_POST['markType'];
        $markText = $_POST['markText'];
        $textSize = $_POST['textSize'];
        $textColor = $_POST['textColor'];
        $position = $_POST['position'];
        
        if($markType == 1)$markText = 'img/qccr.png';
        
        $img = new image();
        $result = $img->watermark($image,$save_image,$markType, $markText, $position, $textSize, $textColor);
        
        if($result['code'] == 0){
            $old_img = getimagesize($image);
            $result['old_info']['size'] = round(filesize($image)/1024,1);
            $result['old_info']['width'] = $old_img[0];
            $result['old_info']['height'] = $old_img[1];
        
            $new_img = getimagesize($save_image);
            $result['new_info']['size'] = round(filesize($save_image)/1024,1);
            $result['new_info']['width'] = $new_img[0];
            $result['new_info']['height'] = $new_img[1];
        }
        
        echo json_encode($result);
        exit;
    }
}





?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<title>图像压缩裁剪水印demo</title>
<link href="static/css/bootstrap.min.css" rel="stylesheet">
<script type="text/javascript" src="static/js/jquery-1.12.1.min.js"></script>
<style type="text/css">
body{margin:0;padding:0;background:#eee;}
.img-thumbnail{border:3px solid #eee;cursor:pointer;}
input{display:inline-block !important;}
</style>
</head>
<body>
<div class="container">
<div class="page-header">
<h3>原始图片：</h3>
</div>
<table width="100%" style="text-align: center;">
    <tr>
        <td><img alt="" src="img/1.jpg" width="200" class="img-thumbnail" rol="1.jpg"><br/>1.jpg</td>
        <td><img alt="" src="img/2.png" width="200" class="img-thumbnail" rol="2.png"><br/>2.png</td>
        <td><img alt="" src="img/3.gif" width="200" class="img-thumbnail" rol="3.gif"><br/>3.gif</td>
    </tr>
</table>
<input type="hidden" id="image" value="" AUTOCOMPLETE="off"/>
<input type="hidden" id="save_image" value="" AUTOCOMPLETE="off"/>
<table class="table table-bordered">
<thead>
  <tr>
    <th colspan="4"><label><input type="radio" value="1" name="type" class="type" checked="checked"> 压缩或裁剪</label>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#999">选中图片：<b class="select_img">无</b></span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#999">保存位置：<b class="save_img">无</b></span></th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>图片质量：<input type="text" value="" name="quality" style="width: 60px;" class="quality form-control"><br/><span style="color: #999;">图片质量从1到9，数字越小图片大小越小，默认按8品质压缩</span></td>
    <td>裁剪方式：<input type="radio" name="is_cut" class="is_cut" value="0" checked="checked"> 不裁剪 <input type="radio" class="is_cut" name="is_cut" value="1"> 按宽高裁剪 <input type="radio" name="is_cut" class="is_cut" value="2"> 按宽高比例裁剪 <br/><span style="color: #999;">按宽高比例裁剪：新图片最高宽度不宽与图片宽度，最高高度不高于图片高度，按等比例裁剪</span></td>
    <td width="170">图片宽度：<input type="text" name="maxWidth" value="" class="form-control maxWidth" style="width: 60px;"> px</td>
    <td width="170">图片高度：<input type="text" name="maxHeight" value="" class="form-control maxHeight" style="width: 60px;"> px</td>
  </tr>
</tbody>
<thead>
  <tr>
    <th colspan="4"><label><input type="radio" value="2" name="type" class="type"> 水印</label>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#999">选中图片：<b class="select_img">无</b></span>&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:#999">保存位置：<b class="save_img">无</b></span></th>
  </tr>
</thead>
<tbody>
  <tr>
    <td>水印方式：<input type="radio" name="markType" class="markType" value="1" checked="checked"> 图片水印 <input type="radio" class="markType" name="markType" value="2"> 文字水印</td>
    <td>水印文字：<input type="text" name="markText" value="汽车超人" class="form-control markText" style="width: 100px;"><br/>水印图片：<img alt="" src="img/qccr.png"></td>
    <td>字体大小：<input type="text" name="textSize" value="12" class="form-control textSize" style="width: 60px;"> px<br/>字体颜色：<input type="text" name="textColor" value="#000000" class="form-control textColor"></td>
    <td>水印位置：<input type="text" name="position" value="1" class="form-control position" style="width: 40px;"><br/><span style="color: #999;">1 顶部居左  2 顶部居中  3 顶部居右  4 中部居左  5 中部居中  6 中部居右  7 底部居左  8 底部居中  9 底部居右  10 随机</span></td>
    
  </tr>
</tbody>
</table>
    <div style="text-align: center;" class="container">
        <p><button type="button" class="btn btn-success submit-cut">压缩或裁剪</button>&nbsp;&nbsp;</p>
    </div>
 
</div>
<table class="table table-bordered" style="text-align: center;">
<tbody>
  <tr>
    <td class="old_img"></td>
    <td class="new_img"></td>
  </tr>
  <tr>
    <td class="old_info"></td>
    <td class="new_info"></td>
  </tr>
</tbody>
</table>
<script type="text/javascript">
$(function(){
	$('.img-thumbnail').click(function(){
		$('.img-thumbnail').css('borderColor','#eee')
		$(this).css('borderColor','#E7BA50')
		$('.select_img').text($(this).attr('rol'));
		$('#image').val('img/'+$(this).attr('rol'));
		$('.save_img').text('2016/'+$(this).attr('rol'));
		$('#save_image').val('2016/'+$(this).attr('rol'));
	});

	$('.submit-cut').click(function(){
		if($('#image').val() == ''){
			alert('请先选择要处理的照片');
			return false;
		}

		if($('.type:checked').val() == 1){
			var data = {
					image:$('#image').val(),
					save_image:$('#save_image').val(),
					quality:$('.quality').val(),
					is_cut:$('.is_cut:checked').val(),
					maxWidth:$('.maxWidth').val(),
					maxHeight:$('.maxHeight').val(),
					type:1
				}
		}else{
			var data = {
					image:$('#image').val(),
					save_image:$('#save_image').val(),
					markType:$('.markType:checked').val(),
					markText:$('.markText').val(),
					textSize:$('.textSize').val(),
					textColor:$('.textColor').val(),
					position:$('.position').val(),
					type:2
				}
		}
		
		$.post('index.php',data,function(result){
			if(result.code != 0){
				alert(result.msg);
				return false;
			}else{
				$('.old_img').html('<img src="'+$('#image').val()+'">');
				$('.old_info').html('宽度：'+result.old_info.width+'&nbsp;&nbsp;'+'高度：'+result.old_info.height+'&nbsp;&nbsp;大小：'+result.old_info.size+'K');
				$('.new_img').html('<img src="'+$('#save_image').val()+'?'+Math.random()+'">');
				$('.new_info').html('宽度：'+result.new_info.width+'&nbsp;&nbsp;'+'高度：'+result.new_info.height+'&nbsp;&nbsp;大小：'+result.new_info.size+'K');
			}
		},'json');
	});
})
</script>
</body>
</html>