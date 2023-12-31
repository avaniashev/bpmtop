<?php

class Watermark
{

    function create_watermark($main_img_obj, $watermark_img_obj, $alpha_level = 100)
    {
        $wmMergeMap = array(
            'to_left'=>2,
            'to_left_offset'=>2,
            'from_bottom'=>2,
            'from_bottom_offset'=>2,
            'alphaLevel'=> 100
        );
        $customMap = Configure::read('Agency.watermark_merge_map');
        if (!empty($customMap)) {
            $wmMergeMap = am($wmMergeMap, $customMap);
            $alpha_level = $wmMergeMap['alphaLevel'];
        }

        $alpha_level /= 100;

        $main_img_obj_w = imagesx($main_img_obj);
        $main_img_obj_h = imagesy($main_img_obj);

        $watermark_img_obj_w = imagesx($watermark_img_obj);
        $watermark_img_obj_h = imagesy($watermark_img_obj);

        $main_img_obj_min_x = floor(($main_img_obj_w / $wmMergeMap['to_left']) - ($watermark_img_obj_w / $wmMergeMap['to_left_offset']));
//        $main_img_obj_max_x = ceil(($main_img_obj_w / 2) + ($watermark_img_obj_w / 2));

        $main_img_obj_min_y = floor(($main_img_obj_h / $wmMergeMap['from_bottom']) - ($watermark_img_obj_h / $wmMergeMap['from_bottom_offset']));
        //floor( ( $main_img_obj_h / 2 ) - ( $watermark_img_obj_h / 2 ) );

//        $main_img_obj_max_y = ceil(($main_img_obj_h / 2) + ($watermark_img_obj_h / 2));

        $return_img = imagecreatetruecolor($main_img_obj_w, $main_img_obj_h);

        for ($y = 0; $y < $main_img_obj_h; $y++) {
            for ($x = 0; $x < $main_img_obj_w; $x++) {
                $return_color = null;
                $watermark_x = $x - $main_img_obj_min_x;
                $watermark_y = $y - $main_img_obj_min_y;
                $main_rgb = imagecolorsforindex($main_img_obj, imagecolorat($main_img_obj, $x, $y));
                $w_cond = $watermark_x >= 0 && $watermark_x < $watermark_img_obj_w;
                $h_cond = $watermark_y >= 0 && $watermark_y < $watermark_img_obj_h;
                if ($w_cond && $h_cond) {
                    $watermark_rbg = imagecolorsforindex($watermark_img_obj,
                        imagecolorat($watermark_img_obj, $watermark_x, $watermark_y));
                    $watermark_alpha = round(((127 - $watermark_rbg['alpha']) / 127), 2);
                    $watermark_alpha = $watermark_alpha * $alpha_level;


                    $avg_red = $this->_get_ave_color($main_rgb['red'], $watermark_rbg['red'], $watermark_alpha);
                    $avg_green = $this->_get_ave_color($main_rgb['green'], $watermark_rbg['green'], $watermark_alpha);
                    $avg_blue = $this->_get_ave_color($main_rgb['blue'], $watermark_rbg['blue'], $watermark_alpha);
                    $return_color = $this->_get_image_color($return_img, $avg_red, $avg_green, $avg_blue);
                } else {
                    $return_color = imagecolorat($main_img_obj, $x, $y);
                }
                imagesetpixel($return_img, $x, $y, $return_color);
            }
        }

        return $return_img;
    }

    function _get_ave_color($color_a, $color_b, $alpha_level)
    {
        return round((($color_a * (1 - $alpha_level)) + ($color_b * $alpha_level)));
    }

    function _get_image_color($im, $r, $g, $b)
    {
        $c = imagecolorexact($im, $r, $g, $b);
        if ($c != -1) {
            return $c;
        }
        $c = imagecolorallocate($im, $r, $g, $b);
        if ($c != -1) {
            return $c;
        }

        return imagecolorclosest($im, $r, $g, $b);
    }
}