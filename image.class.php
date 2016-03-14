<?php
class image
{
    private $interlace = 0;
    private $water_font = 'font/2.TTF';  //1.ttf 仿宋  2.ttf 华文隶书  3.ttf 黑体
    
    public function __construct(){
        if(!function_exists('imagejpeg')){
            return array('code'=>1, 'msg'=>'GD扩展未开启');
        }
    }
    
    /**
     * 图像压缩、裁剪
     * @param string $image      目标图像地址
     * @param string $savePath   新图像保存地址，带文件名（默认覆盖原图）
     * @param number $quality    图像品质，从1-9 （数值越低越不清晰文件越小）
     * @param number $is_cut     是否进行裁剪，1 以$maxwidth和$maxheight进行裁剪，2 以$maxwidth和$maxheight按比例裁剪
     * @param number $maxwidth   最宽宽度
     * @param number $maxheight  最高高度
     * @return multitype:number string
     */
    public function thumb($image,$savePath='', $quality = 8, $is_cut = 0, $maxwidth = 200, $maxheight = 200){
        
        if(!file_exists($image)){
            return array('code'=>2, 'msg'=>'图片不存在');
        }
        
        if(empty($savePath))$savePath = $image;
        
        $this->mk_mkdir(dirname($savePath));
        
        if(!is_dir(dirname($savePath))){
            return array('code'=>3, 'msg'=>'保存目录不存在');
        }
        
        $imageInfo = @getimagesize($image);
        if(!$imageInfo){
            return array('code'=>4, 'msg'=>'不是一个有效的图像');
        }
        
        if($quality < 0 || $quality > 9){
            return array('code'=>7, 'msg'=>'不是一个正确的图像品质');
        }
        
        if($is_cut > 0 && ($maxwidth <= 0 || $maxheight <=0)){
            return array('code'=>8, 'msg'=>'裁剪时宽或高不能小于0');
        }
        
        $imageName = basename($image);
        $imageArr = explode('.',$imageName);
        
        $imageType = explode('.', basename($savePath))[1];
        
        switch ($imageType){
            case 'jpg':
                $image_fun = 'imagejpeg';
                break;
            case 'jpeg':
                $image_fun = 'imagejpeg';
                break;
            case 'png':
                $image_fun = 'imagepng';
                break;
            case 'gif':
                $image_fun = 'imagepng';
                break;
            case 'bmp':
                $image_fun = 'imagewbmp';
                break;
            default:
                return array('code'=>4,'msg'=>'不支持图片格式');
                break;
        }
        
        switch ($imageArr[1]){
            case 'jpg':
                $im = imagecreatefromjpeg($image);
                break;
            case 'jpeg':
                $im = imagecreatefromjpeg($image);
                break;
            case 'png':
                $im = imagecreatefrompng($image);
                break;
            case 'gif':
                $im = imagecreatefromgif($image);
                break;
            case 'bmp':
                $im = imagecreatefromwbmp($image);
                break;
            default:
                return array('code'=>5,'msg'=>'不支持图片格式');
                break;
        }
        
        if(!function_exists($image_fun)){
            return array('code'=>6, 'msg'=>'GD扩展没开启');
        }
        
        $thumbimg = $im;
        
        if($is_cut > 0 && $maxwidth > 0 && $maxheight > 0){
            //裁剪
            if($is_cut == 1){
                $width = $maxwidth;
                $height = $maxheight;
            }elseif($is_cut == 2){
                $newInfo = $this->compute($imageInfo[0],$imageInfo[1],$maxwidth,$maxheight);
                $width = $newInfo[0];
                $height = $newInfo[1];
            }
            
            if($imageType != 'gif' && function_exists('imagecreatetruecolor')){
                $thumbimg = imagecreatetruecolor($width, $height);
            }else{
                $thumbimg = imagecreate($width, $height);
            }
            
            if($imageType=='gif' || $imageType=='png') {
                /*$background_color  =  imagecolorallocate($thumbimg,  0, 255, 0);  //  指派一个绿色
                 imagecolortransparent($thumbimg, $background_color);  //  设置为透明色，若注释掉该行则输出绿色的图*/
                $alpha = imagecolorallocatealpha($thumbimg, 0, 0, 0, 127);
                imagefill($thumbimg, 0, 0, $alpha);
            }
            
            if(function_exists('imagecopyresampled')){
                imagecopyresampled($thumbimg, $im, 0, 0, 0, 0, $width, $height, $imageInfo[0], $imageInfo[1]);
            }else{
                imagecopyresized($thumbimg, $im, 0, 0, 0, 0, $width, $height, $imageInfo[0], $imageInfo[1]);
            }
            
            
            if($imageType=='jpg' || $imageType=='jpeg') imageinterlace($thumbimg, $this->interlace);
        }        
        
        imagesavealpha($thumbimg, true);
        if((intval($quality) > 0 && $image_fun == 'imagejpeg')){
            $result = $image_fun($thumbimg,$savePath, $quality*10);
        }elseif ((intval($quality) > 0 && $image_fun == 'imagepng')){
            $result = $image_fun($thumbimg,$savePath, 10-$quality);
        }else{
            $result = $image_fun($thumbimg,$savePath);
        }
        
        imagedestroy($thumbimg);
        return $result ? array('code'=>0,'msg'=>'图像处理成功') : array('code'=>99,'msg'=>'图像处理失败');
    }
    
    /**
     * 给图像添加水印
     * @param string $image       目标图像地址
     * @param string $savePath    保存地址，带文件名（默认覆盖原图）
     * @param string $markType    水印方式  1 图片水印  2 文字水印
     * @param string $w_content   水印图片或水印文字
     * @param number $w_position  水印位置  1 顶部居左  2 顶部居中  3 顶部居右  4 中部居左  5 中部居中  6 中部居右  7 底部居左  8 底部居中  9 底部居右  10 随机
     * @param number $w_font      字体大小
     * @param string $w_color     字体颜色
     * @return void|multitype:number string |boolean
     */
    public function watermark($image,$savePath='', $markType = '2', $w_content = '汽车超人', $w_position=10, $w_font = 12, $w_color = '#000000'){
        if(!file_exists($image)){
            return array('code'=>2, 'msg'=>'图片不存在');
        }
        
        if(empty($savePath))$savePath = $image;
        
        $this->mk_mkdir(dirname($savePath));
        
        if(!is_dir(dirname($savePath))){
            return array('code'=>3, 'msg'=>'保存目录不存在');
        }
        
        $imageInfo = @getimagesize($image);
        if(!$imageInfo){
            return array('code'=>4, 'msg'=>'不是一个有效的图像');
        }
        
        switch ($imageInfo[2]){
            case 1:
                $im = imagecreatefromgif($image);
                break;
            case 2:
                $im = imagecreatefromjpeg($image);
                break;
            case 3:
                $im = imagecreatefrompng($image);
                break;
            case 6:
                $im = imagecreatefromwbmp($image);
                break;
            default:
                return array('code'=>5,'msg'=>'不支持图片格式');
                break;
        }
        
        if($markType == 1 && file_exists($w_content)){
            $ifwaterimage = 1;
            $water_info   = @getimagesize($w_content);
            $width        = $water_info[0];
            $height       = $water_info[1];
            switch($water_info[2]) {
                case 1 :
                    $water_img = imagecreatefromgif($w_content);
                    break;
                case 2 :
                    $water_img = imagecreatefromjpeg($w_content);
                    break;
                case 3 :
                    $water_img = imagecreatefrompng($w_content);
                    break;
                default:
                    return array('code'=>6,'msg'=>'水印图片不是有效的图片');
                    break;
            }
        }elseif ($markType == 2){
            $ifwaterimage = 0;
            $temp = imagettfbbox($w_font, 0, $this->water_font, $w_content);
            $width = max($temp[2], $temp[4]) - min($temp[0], $temp[6]);
            $height = max($temp[1], $temp[3]) - min($temp[5], $temp[7]);
            $ax = min($temp[0], $temp[6]) * -1;
            $ay = min($temp[5], $temp[7]) * -1;
            unset($temp);
        }
        
        switch($w_position) {
            case 1:
                $wx = +5;
                $wy = +5;
                break;
            case 2:
                $wx = ($imageInfo[0] - $width) / 2;
                $wy = 0;
                break;
            case 3:
                $wx = $imageInfo[0] - $width;
                $wy = 0;
                break;
            case 4:
                $wx = 0;
                $wy = ($imageInfo[1] - $height) / 2;
                break;
            case 5:
                $wx = ($imageInfo[0] - $width) / 2;
                $wy = ($imageInfo[1] - $height) / 2;
                break;
            case 6:
                $wx = $imageInfo[0] - $width;
                $wy = ($imageInfo[1] - $height) / 2;
                break;
            case 7:
                $wx = 0;
                $wy = $imageInfo[1] - $height;
                break;
            case 8:
                $wx = ($imageInfo[0] - $width) / 2;
                $wy = $imageInfo[1] - $height;
                break;
            case 9:
                $wx = $imageInfo[0] - $width;
                $wy = $imageInfo[1] - $height;
                break;
            case 10:
                $wx = rand(0,($imageInfo[0] - $width));
                $wy = rand(0,($imageInfo[1] - $height));
                break;
            default:
                $wx = rand(0,($imageInfo[0] - $width));
                $wy = rand(0,($imageInfo[1] - $height));
                break;
        }
        
        if($ifwaterimage) {
            //if($water_info[2] == 3) {
                imagecopy($im, $water_img, $wx, $wy, 0, 0, $width, $height);
            /*} else {
                imagecopymerge($im, $water_img, $wx, $wy, 0, 0, $width, $height, $this->w_pct);
            }*/
        } else {
            if(!empty($w_color) && (strlen($w_color)==7)) {
                $r = hexdec(substr($w_color,1,2));
                $g = hexdec(substr($w_color,3,2));
                $b = hexdec(substr($w_color,5));
            } else {
                return;
            }
            //$w_content = iconv("UTF-8", "GB2312", $w_content);
            imagettftext($im,$w_font,0,$wx+$ax,$wy+$ay,imagecolorallocate($im,$r,$g,$b),$this->water_font,$w_content);
        }
        
        switch($imageInfo[2]) {
            case 1 :
                $result = imagegif($im, $savePath);
                break;
            case 2 :
                $result = imagejpeg($im, $savePath, 100);
                break;
            case 3 :
                $result = imagepng($im, $savePath);
                break;
            case 6 :
                $result = imagewbmp($im, $savePath);
                break;
            default :
                return;
        }
        
        if(isset($water_info)) {
            unset($water_info);
        }
        if(isset($water_img)) {
            imagedestroy($water_img);
        }
        unset($source_info);
        imagedestroy($im);
        return $result ? array('code'=>0,'msg'=>'图像处理成功') : array('code'=>99,'msg'=>'图像处理失败');
    }
    
    /**
     * 处理图片实际应设置的宽高
     * @param int $width      图像的宽度
     * @param int $height     图像的高度
     * @param int $maxwidth   最大宽度
     * @param int $maxheight  最大高度
     * @return array      
     */
    private function compute($width,$height,$maxwidth,$maxheight){
        $newWidth = $width;
        $newHeight = $height;
        if($width >= $height){  //宽 >= 高
            if($width >= $maxwidth){
                $newWidth = $maxwidth;
                $newHeight = intval(($newWidth/$width)*$height);
                if($newHeight > $maxheight){
                    $newWidth = intval(($maxheight/$newHeight)*$newWidth);
                    $newHeight = $maxheight;
                }
            }elseif ($height >= $maxheight){
                $newWidth = intval(($maxheight/$newHeight)*$newWidth);
                $newHeight = $maxheight;
            }
        }else{  //宽 < 高
            if($height >= $maxheight){
                $newHeight = $maxheight;
                $newWidth = intval(($maxheight/$height)*$width);
                if($newWidth > $maxwidth){
                    $newHeight = intval(($maxwidth/$newWidth)*$newHeight);
                    $newWidth = $maxwidth;
                }
            }elseif ($width >= $maxwidth){
                $newHeight = intval(($maxwidth/$width)*$height);
                $newWidth = $maxwidth;
            }
        }
        return array($newWidth,$newHeight);
    }
    
    /**
     * 创建目录
     *
     * @param	string	$path	路径
     * @param	string	$mode	属性
     * @return	string	如果已经存在则返回true，否则为flase
     */
    public function mk_mkdir($path, $mode = 0777) {
        if(is_dir($path)) return TRUE;
        $ftp_enable = 0;
        $path = $this->dir_path($path);
        $temp = explode('/', $path);
        $cur_dir = '';
        $max = count($temp) - 1;
        for($i=0; $i<$max; $i++) {
            $cur_dir .= $temp[$i].'/';
            if (@is_dir($cur_dir)) continue;
            @mkdir($cur_dir, 0777,true);
            @chmod($cur_dir, 0777);
        }
        return is_dir($path);
    }
    
    /**
     * 转化 \ 为 /
     *
     * @param	string	$path	路径
     * @return	string	路径
     */
    private function dir_path($path) {
        $path = str_replace('\\', '/', $path);
        if(substr($path, -1) != '/') $path = $path.'/';
        return $path;
    }
    
}