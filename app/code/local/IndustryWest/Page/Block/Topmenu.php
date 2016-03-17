<?php

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category category
 * @package  package
 * @copyright  Copyright (c) 2015  NarfStudios (http://www.narfstudios.de)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 *
 * @category category
 * @package  IndustryWest
 * @author   Trevor Wagner <trevor@cobblehilldigital.com>
 */
class IndustryWest_Page_Block_Topmenu extends Mage_Page_Block_Html_Topmenu
{

  public function getHtml( $links )
  {
    $html = '<ul class="inline left">';

    // GET CATEGORY NAVIGATION
    $html .= parent::getHtml( $links );

    // APPEND EXTRA CMS LINKS (ASSUMING BLOCK EXISTS "navigation_links_left_side")
    $block = $this->getLayout()->createBlock('cms/block')->setBlockId("main_nav_left_side");

    if ( is_object( $block ) && $block->getIsActive() )
    {
      $html .= $block->toHtml();
    }

    $html .= '</ul>';

    $html .= '<ul class="inline right">';

    // APPEND EXTRA CMS LINKS (ASSUMING BLOCK EXISTS "navigation_links_left_side")
    $block = $this->getLayout()->createBlock('cms/block')->setBlockId("main_nav_right_side");

    if ( is_object( $block ) && $block->getIsActive() )
    {
      $html .= $block->toHtml();
    }

    $html .= '</ul>';

    return $html;

  }

}
