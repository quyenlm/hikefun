<?php
/******************************************************
 * @package Pavothemer for Opencart 3.x
 * @version 1.0
 * @author http://www.pavothemes.com
 * @copyright   Copyright (C) Feb 2017 PavoThemes.com <@emai:pavothemes@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class PA_Widget_Promotion_Category extends PA_Widgets {

    public function fields() {
        $this->load->model('catalog/category');
        $get_category = $this->model_catalog_category->getCategories();
        $categories = array();
        foreach ($get_category as $cat_id) {
            $categories[] = array(
                'value' => $cat_id['category_id'],
                'label' => $cat_id['name']
            );
        }
        return array(
            'mask'		=> array(
                'icon'	=> 'fa fa-id-card-o',
                'label'	=> $this->language->get( 'entry_promotions_category' )
            ),
            'tabs'	=> array(
                'general'		=> array(
                    'label'		=> $this->language->get( 'entry_general_text' ),
                    'fields'	=> array(
                        array(
                            'type'  => 'hidden',
                            'name'  => 'uniqid_id',
                            'label' => $this->language->get( 'entry_row_id_text' ),
                            'desc'  => $this->language->get( 'entry_column_desc_text' )
                        ),
                        array(
                            'type'  => 'text',
                            'name'  => 'extra_class',
                            'label' => $this->language->get( 'entry_extra_class_text' ),
                            'default' => '',
                            'desc'  => $this->language->get( 'entry_extra_class_desc_text' )
                        ),
                        array(
                            'type'      => 'text',
                            'name'      => 'title_promo',
                            'label'     => $this->language->get( 'entry_title_text' ),
                            'desc'      => $this->language->get( 'entry_title_desc_text' ),
                            'default'     => '',
                            'language'  => true
                        ),
                        array(
                            'type'      => 'editor',
                            'name'      => 'subtitle_promo',
                            'label'     => $this->language->get( 'entry_subtitle_text' ),
                            'default'     => '',
                            'language'  => true
                        ),
                        array(
                            'type'  => 'group', 
                            'name'  => 'items',
                            'label' => $this->language->get( 'entry_item' ),
                            'fields'    => array(
                                array(
                                    'type'      => 'select',
                                    'name'      => 'category',
                                    'label'     => $this->language->get( 'entry_list_category' ),
                                    'default'   => '',
                                    'options'   => $categories,
                                ),
                                array(
                                    'type'  => 'image',
                                    'name'  => 'banner_image',
                                    'label' => $this->language->get( 'entry_banner_image' )
                                ),
                                array(
                                    'type'  => 'text',
                                    'name'  => 'banner_size',
                                    'label' => $this->language->get( 'entry_banner_image_size' ),
                                    'desc'  => $this->language->get( 'entry_banner_image_desc' ),
                                    'default'       => 'full',
                                    'placeholder'   => '200x400'
                                ),
                            )
                        )
                    )
                ),
                'style'				=> array(
                    'label'			=> $this->language->get( 'entry_styles_text' ),
                    'fields'		=> array(
                        array(
                            'type'	=> 'layout-onion',
                            'name'	=> 'layout_onion',
                            'label'	=> 'entry_box_text'
                        ),
                        array(
                            'type'	=> 'colorpicker',
                            'name'	=> 'color',
                            'label'	=> 'entry_color_text'
                        )
                    )
                )
            )
        );
    }

    public function render( $settings = array(), $content = '' ) {
        $this->load->model('catalog/category');
        $this->load->model( 'tool/image' );

        $class = array();
        if ( ! empty( $settings['extra_class'] ) ) {
            $class[] = $settings['extra_class'];
        }
        if ( ! empty( $settings['effect'] ) ) {
            $class[] = $settings['effect'];
        }
        $settings['class']  = implode( ' ', $class );

        if( defined("IMAGE_URL")){
            $server =  IMAGE_URL;
        } else  {
            $server = ($this->request->server['HTTPS'] ? HTTPS_SERVER : HTTP_SERVER).'image/';
        }

        $settings['server'] = $server;

        $settings['subtitle_promo'] = ! empty( $settings['subtitle_promo'] ) ? html_entity_decode( htmlspecialchars_decode( $settings['subtitle_promo'] ), ENT_QUOTES, 'UTF-8' ) : '';
        $get_item = array ();
        if (!empty ($settings['items'])) {
            foreach ($settings['items'] as $value) {
                if ( ! empty( $value['banner_image'] ) ) {
                    $value['banner_size'] = strtolower( $value['banner_size'] );
                    $src = empty( $value['banner_size'] ) || $value['banner_size'] == 'full' ? $server . $value['banner_image'] : false;

                    if ( $src === false && strpos( $value['banner_size'], 'x' ) ) {
                        $src = $this->getImageLink($value['banner_image'], $value['banner_size']);
                    }
                
                    $value['banner_image'] = $src ? $src : '';
                }

                if (!empty ($value['category'])) {
                    $get_cat = $this->model_catalog_category->getCategory($value['category']);
                    $get_item[] = array (
                        'category_id'   => isset($get_cat['category_id']) ? $get_cat['category_id'] : '',
                        'category_name' => isset($get_cat['name']) ? $get_cat['name'] : '',
                        'category_image'    => isset($value['banner_image']) ? $value['banner_image'] : '',
                        'category_link'     => $this->url->link('product/category','path=' .(isset($get_cat['category_id']) ? $get_cat['category_id'] : '')),
                    );
                }
            }
        }

        $settings['get_item'] = $get_item;
        
        $args = 'extension/module/pavobuilder/pa_promotion_category/pa_promotion_category';
        return $this->load->view( $args, array( 'settings' => $settings, 'content' => $content ) );
    }

}