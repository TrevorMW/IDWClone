<?php
/**
 * OnlineBiz_MageMenu_Block_Menuitem
 *
 * @package OnlineBiz_MageMenu

 * @version 1.5.0
 *
 */
class OnlineBiz_MageMenu_Block_Menuitem extends Mage_Core_Block_Template
{
  protected $_menuInstance = null;
  protected $_parentId = null;
  protected $_block = null;

  protected function _construct()
  {
    $this->addData(array(
        'cache_lifetime'    => false,
        'cache_tags'        => array(
          OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG,
          Mage_Catalog_Model_Product::CACHE_TAG,
          OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG,
          Mage_Cms_Model_Block::CACHE_TAG,
          OnlineBiz_MageMenu_Model_Menuitem::CACHE_TAG,
          Mage_Core_Model_Store_Group::CACHE_TAG
        ),
      ));
  }


  /**
   * Retrieve Key for caching block content
   *
   * @return string
   */
  public function getCacheKey()
  {
    $isHttps = '';
    if(Mage::getModel('core/store')->isCurrentlySecure())
    {
      $isHttps = '_https';
    }
    return  $isHttps . $this->generateBlockName(array($this->getMenuCode(), $this->getName(), $this->getAs()))
      . '_' . Mage::app()->getStore()->getId()
      . '_' . Mage::getDesign()->getPackageName()
      . '_' . Mage::getDesign()->getTheme('template')
      . '_' . md5($this->getTemplate());
  }

  public function getParentId()
  {
    return $this->getInstance()->getId();
  }

  public function setInstance($instance)
  {
    $this->setData('instance', $instance);
    $this->addData($instance->getData());

    return $this;
  }

  public function getInstance()
  {
    if(is_null($this->getData('instance'))) {
      $instance = Mage::getModel('magemenu/menuitem');
      if($this->getMenuCode()) {
        $this->setInstance($instance->loadByAttribute('menu_code', (string) $this->getMenuCode()));
      } else {
        $this->setInstance($instance);
      }
    }

    return $this->getData('instance');
  }

  /**
   * Get catagories of current store
   *
   * @return Varien_Data_Tree_Node_Collection
   */
  public function getMenuNodes()
  {
    if(!Mage::helper('magemenu')->isActivated())
      return array();
    $helper = Mage::helper('magemenu/menuitem');
    $helper->setParentId($this->getParentId());
    return $helper->getMenuNodes();
  }


  /**
   * Get catagories of current store
   *
   * @return Varien_Data_Tree_Collection
   */
  public function getMenuCollection()
  {
    $helper = Mage::helper('magemenu/menuitem');
    $helper->setParentId($this->getParentId());
    return $helper->getMenuNodes(false, true);
  }


  /**
   * Retrieve child menus of current category
   *
   * @return Varien_Data_Tree_Node_Collection
   */
  public function getCurrentChildNodes()
  {
    if( ! $this->getMenuCode()) {
      return;
    }
    return $this->getInstance();
  }


  /**
   * Checkin activity of category
   *
   * @param   Varien_Object $menuitem
   * @return  bool
   */
  public function isCategoryActive($menuitem)
  {
    if(null === Mage::registry('magemenu_current_path')) {

      if(Mage::registry('current_category')) {
        $linkType = 'category';
        $linkId = Mage::registry('current_category')->getId();
      }

      if( ! isset($linkId) && Mage::registry('current_product')) {
        $linkType = 'product';
        $linkId = Mage::registry('current_product')->getSku();
      }

      if( ! isset($linkId) && Mage::app()->getRequest()->getParam('page_id')) {
        $linkType = 'cms_page';
        $linkId = Mage::app()->getRequest()->getParam('page_id');
      }

      $path = array();
      if( isset($linkId) && isset($linkType)) {
        $nodes = Mage::getModel('magemenu/menuitem')->getCollection()
        ->addFieldToFilter('link_to', $linkType)
        ->addFieldToFilter('link_to_' . $linkType, $linkId);

        foreach($nodes as $node) {
          $path = array_merge($path, explode('/', $node->getData('path')));
        }
      }
      Mage::register('magemenu_current_path', $path);
    }

    return Mage::registry('magemenu_current_path') ? in_array($menuitem->getId(), Mage::registry('magemenu_current_path')) : false;
  }


  /**
   * Get url for category data
   *
   * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
   * @return string
   */
  public function getMenuitemUrl($menuitem)
  {
    if ($menuitem instanceof OnlineBiz_MageMenu_Model_Menuitem) {
      $url = $menuitem->getUrl();
    } else {
      $url = $this->getModel()
      ->setData($menuitem->getData())
      ->getUrl();
    }

    return $url;
  }


  public function getModel($data = null)
  {
    if(is_null($this->_model)) {
      $this->_model = Mage::getModel('magemenu/menuitem');
    }

    if( ! is_null($data)) {
      $this->_model->setData($data);
    }
    return $this->_model;
  }

  public function getMenu($asArray = false)
  {
    if(!Mage::helper('magemenu')->isActivated())
      return '';
    $nodes = $this->getMenuNodes();
    $nodeCount = 0;
    $html = $asArray ? array() : '';
    foreach ($nodes as $key => $_node) {
      $node = $this->drawItem($_node, 0, ++$nodeCount == $nodes->count());
      if($asArray) {
        $html[] = $node->toHtml();
      } else {
        $html.= $node->toHtml();
      }
    }
    return $html;
  }

  public function generateBlockName($array = array())
  {
    $name = implode('_', $array);
    while(in_array($name, array_keys($this->getLayout()->getAllBlocks()))) {
      $name .= rand(1,9);
    }

    return $name;
  }

  public function getBlock()
  {
    if(is_null($this->_block)) {

      $this->_block = $this->getLayout()->createBlock(
        'magemenu/menuitem_node',
        $this->generateBlockName(array($this->getMenuCode(), $this->getName(), $this->getAs()))
      );

      $this->_block->addElementOptions('class', 'div', array(
          $this->getData('custom_css_class'),
        ));

      $this->_block->addElementOptions('style', 'div', array(
          'background-color' => '#' . $this->getData('background_color'),
        ));

      if($this->getData('image')) {
        $this->_block->addElementOptions('style', 'div', array(
            'background-image' => 'url(' . Mage::getBaseUrl('media').'catalog/category/'.$this->getData('image').')',
          ));
      }
    }
    return $this->_block;
  }

  /**
   *
   *
   * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
   * @param int $level
   * @param boolean $last
   * @return string
   */
  public function drawItem($node, $level=0, $last=false)
  {
    $menuNode = $this->getModel($node->getData());
    $block = $this->getLayout()->createBlock(
      'magemenu/menuitem_node',
      $this->generateBlockName(array($this->getMenuCode(), 'node', $menuNode->getId(), $this->getName(), $this->getAs()))
    );

    $block->setIsActive($menuNode->isActive());

    if (!$block->isActive()) {
      return $block;
    }
    $blockId = $node->getCmsblock(); // --- static block key
    if($blockId){
      $blockHtml = Mage::getModel('core/layout')->createBlock('cms/block')->setBlockId($blockId);
    }
    $block->setLevel($level);
    if($last) {
      $block->addElementOptions('class', 'li', array('last'));
    }
    $block->addElementOptions('attribute', 'li.a', array(
        'href' => $menuNode->getUrl(true),
        'target' => $menuNode->getLinkTarget(),
      ));

    $isActive = $this->isCategoryActive($node) ? 'active' : null;
    $block->addElementOptions('class', 'li.a', array("level{$level}", $isActive));

    $cssUrl = Mage::helper('catalog/category')->getCategoryUrlPath(str_replace(array(Mage::getBaseUrl(), '#'), '',$menuNode->getUrl(false)));
    $block->addElementOptions('class', 'li', array(
        'level'.$level,
        'nav-'.str_replace('/', '-', $cssUrl),
        $menuNode->getData('custom_css_class'),
        $isActive,
      ));

    $block->addElementOptions('style', 'li.a.span', array(
        'color' => '#' . $menuNode->getData('font_color'),
        'font-weight' => $menuNode->getData('font_weight'),
      ));
    $block->addElementOptions('class', 'li.a.span', array("level{$level}", $isActive));

    $hasImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackgroundImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasIcon = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());

    $block->addElementOptions('style', 'li.a', array(
        'background-image' => $hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : null,
        'background-position' => $hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : null,
        'background-repeat' => $this->getImageAsBackground() && $hasImage ? 'no-repeat' : null,
      ));

    if($hasIcon) {
      $block->addElementOptions(array(
          'li.a.img' => array(
            'class'  => array('image', 'level'.$level),
            'attribute' => array('src' => $menuNode->getImageUrl()),
          ),
          'li.a.span' => array(
            'class' => array('icon'),
          )
        ));
    }

    if( ! $this->getImageAsBackground() && $hasImage) {
      $block->setName('<img src="' . $menuNode->getImageUrl() . '" />');
    } else {
      $block->setName( ! $hasImage ? $this->htmlEscape($node->getName()) : '&nbsp;');
    }

    $children = $node->getChildren();
    $childrenCount = $children->count();

    // Handle children
    if ($children && $childrenCount){
      $childBlocks = array();
      foreach ($children as $child) {
        $childblock = $this->drawItem($child, $level+1);
        if($childblock->isActive()) {
          $childBlocks[] = $childblock;
        }
      }
      // Mark node as last
      if(isset($childBlocks[sizeof($childBlocks) - 1])) {
        $childBlocks[sizeof($childBlocks) - 1]->addElementOptions('class', 'li', array('last'));
      }

      if(count($childBlocks)) {

        /*
          $block->addElementOptions('attribute', 'li', array(
            'onmouseover' => "toggleMenuCreatorMenu(this,1)",
            'onmouseout' => "toggleMenuCreatorMenu(this,0)",
          ));

        */


        $block->addElementOptions('class', 'li', array('parent'));
        $block->addElementOptions('class', 'li.a', array('parent'));
        $block->addElementOptions('class', 'li.a.span', array('parent'));
        if($blockId){
          $block->setChildren(array_merge($childBlocks, array($blockHtml)));
        } else {
          $block->setChildren($childBlocks);
        }
      } else {
        if($blockId){
          $block->setChildren(array($blockHtml));
          $block->setCmsBlockStyle('width:370px; padding:10px');
          $block->setCmsBlockClass(' static-block');
        }
      }

    }
    else {
      /*$block->addElementOptions('attribute', 'li', array(
          'onmouseover' => "$(this).addClassName('over')",
          'onmouseout' => "$(this).removeClassName('over')",
        ));*/

      if($blockId){
        $block->setChildren(array($blockHtml));
        $block->setCmsBlockStyle('width:370px; padding:10px');
        $block->setCmsBlockClass(' static-block');
      }

    }

    return $block;
  }
  /**
   * Add project specific formatting
   *
   * @param integer $level
   * @param array $levelClass
   * @return string
   */

  public function drawTreeMenuItem($node, $level=0, array $levelClass=null)
  {
    $html = array();

    $menuNode = $this->getModel($node->getData());

    if (! isset($levelClass)) $levelClass = array();
    $combineClasses = array();
    $color = $menuNode->getData('font_color');
    $fontWeight = $menuNode->getData('font_weight');
    $hasImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackgroundImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackground = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode'));
    $hasIcon = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    // indent HTML!

    if($hasIcon) {
      $html[1] = str_pad( "", (($level * 2 ) + 4), " " ).'<a href="'.$menuNode->getUrl(true).'"><img src="'.$menuNode->getImageUrl().'"/><span style="color:#'.$color.'; font-weight:'.$fontWeight.'">'.$this->htmlEscape($menuNode->getName()).'</span></a>'."</span>\n";
    } elseif($hasImage) {
      $html[1] = str_pad( "", (($level * 2 ) + 4), " " ).'<a href="'.$menuNode->getUrl(true).'"><img src="'.$menuNode->getImageUrl().'"/></span></a>'."</span>\n";
    } elseif($hasBackgroundImage || $hasBackground) {
      $style = 'background-image:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : '').';background-position:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : '').';background-repeat:'.($this->getImageAsBackground() && $hasImage ? 'no-repeat' : '').';color:' .'#' . $color.'; font-weight:'.$fontWeight;
      $html[1] = str_pad( "", (($level * 2 ) + 4), " " ).'<a href="'.$menuNode->getUrl(true).'"><span style="'.$style.'">'.$this->htmlEscape($menuNode->getName()).'</span></a>'."</span>\n";

    } else {
      $html[1] = str_pad( "", (($level * 2 ) + 4), " " ).'<a href="'.$menuNode->getUrl(true).'"><span style="color:#'.$color.'; font-weight:'.$fontWeight.'">'.$this->htmlEscape($menuNode->getName()).'</span></a>'."</span>\n";

    }

    $activeTreeChildren = $node->getChildren();
    foreach($activeTreeChildren as $child)
      $activeChildren[] = $child;

    $hasChildren = $activeTreeChildren && ($childrenCount = count($activeTreeChildren));
    if ($hasChildren)
    {
      $htmlChildren = '';

      foreach ($activeTreeChildren as $i => $child)
      {
        $class = array();
        if ($childrenCount == 1)
        {
          $class[] = 'only';
        }
        else
        {
          if (! $i) $class[] = 'first';
          if ($i == $childrenCount-1) $class[] = 'last';
        }

        $htmlChildren.= $this->drawTreeMenuItem($child, $level+1, $class);
      }

      if (!empty($htmlChildren))
      {
        $levelClass[] = 'haveContainer';

        // indent HTML!
        $html[2] = str_pad( "", ($level * 2 ) + 2, " " ).'<div class="itemContainer">'."\n"
          .$htmlChildren."\n".
          str_pad( "", ($level * 2 ) + 2, " " ).'</div>';
      }
    }

    $combineClasses[] = 'treeItem '.$menuNode->getData('custom_css_class');
    if ($this->isCategoryActive($node))
    {
      if(in_array('haveContainer', $levelClass)){
        $combineClasses[] = 'expanded activeTreeItem';
      } else {
        $combineClasses[] = 'activeTreeItem';
      }
    }

    $levelClass = array_merge( $combineClasses, $levelClass);

    // indent HTML!
    $html[0] = str_pad( "", ($level * 2 ) + 2, " " ).sprintf('<p class="%s">', implode(" ", $levelClass))."\n";

    // indent HTML!
    $html[3] = "\n".str_pad( "", ($level * 2 ) + 2, " " ).'</p>'."\n";

    ksort($html);
    return implode('', $html);
  }
  public function drawResponsiveMenuItem($node, $level = 0, $last = false)
  {
    $menuNode = $this->getModel($node->getData());
    $html = array();
    $id = $menuNode->getId();
    // --- Static Block ---
    $blockId = $node->getCmsblock(); // --- static block key
    $blockHtml = Mage::getModel('core/layout')->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    $hasImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackgroundImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackground = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode'));
    $hasIcon = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $activeTreeChildren = $node->getChildren();
    foreach($activeTreeChildren as $child)
      $activeChildren[] = $child;
    $active = $this->isCategoryActive($node) ? ' active' : null;
    $drawPopup = ($blockHtml || count($activeChildren));
    if ($drawPopup)
    {
      $html[] = '<li class="dropdown parent ' . $menuNode->getData('custom_css_class') . $active . '">';
    }
    else
    {
      $html[] = '<li class="dropdown ' . $menuNode->getData('custom_css_class') . $active . '">';
    }
    $color = $menuNode->getData('font_color');
    $fontWeight = $menuNode->getData('font_weight');
    $html[] = '<a class="dropdown-toggle" href="'.$menuNode->getUrl(true).'" >';
    $name = $this->escapeHtml($node->getName());
    if($hasIcon) {
      $html[] = '<img src="'.$menuNode->getImageUrl().'"/><span style="color:#'.$color.'; font-weight:'.$fontWeight.'">' . $name . '</span>';
    } elseif($hasImage) {
      $html[] = '<span><img src="'.$menuNode->getImageUrl().'"/></span>';
    } elseif($hasBackgroundImage || $hasBackground) {
      $style = 'background-image:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : '').';background-position:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : '').';background-repeat:'.($this->getImageAsBackground() && $hasImage ? 'no-repeat' : '').';color:' .'#' . $color.'; font-weight:'.$fontWeight;
      $html[] = '<span style="'.$style.'">'.$name.'</span>';
    } else {
      $html[] = '<span style="color:#'.$color.'; font-weight:'.$fontWeight.'">'.$name.'</span>';
    }
    $html[] = '</a>';
    if ($drawPopup)
    {
      $html[] = '<span class="icon-plus expand"></span>';
    }

    $noCol = $this->getColCount($activeChildren);
    if ($drawPopup)
    {
      if (count($activeChildren))
      {
        $html[] = $this->drawColumns($activeChildren, true);
      }
      if ($blockHtml)
      {

        $html[] = '<div class="sublevel-column-'.$noCol.'"><div class="magemenu-column-0"><div class="static-block">'.$blockHtml.'</div></div></div>';

      }
    }
    $html[] = '</li>';
    $html = implode("\n", $html);
    return $html;
  }
  public function drawExplodedMenuItem($node, $level = 0, $last = false)
  {
    $menuNode = $this->getModel($node->getData());
    $html = array();
    $id = $menuNode->getId();
    // --- Static Block ---
    $blockId = $node->getCmsblock(); // --- static block key
    $blockHtml = Mage::getModel('core/layout')->createBlock('cms/block')->setBlockId($blockId)->toHtml();
    $hasImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackgroundImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $hasBackground = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode'));
    $hasIcon = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
    $activeTreeChildren = $node->getChildren();
    foreach($activeTreeChildren as $child)
      $activeChildren[] = $child;
    $active = $this->isCategoryActive($node) ? ' act' : null;
    $drawPopup = ($blockHtml || count($activeChildren));
    if ($drawPopup)
    {
      $html[] = '<li id="menu' . $id . '" class="menu' . $menuNode->getData('custom_css_class') . $active . '" onmouseover="showMenuPopup(this, \'popup' . $id . '\');" onmouseout="hideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
    }
    else
    {
      $html[] = '<li id="menu' . $id . '" class="menu' . $menuNode->getData('custom_css_class') . $active . '"onmouseover="showMenuPopup(this, \'0\');" onmouseout="hideMenuPopup(this, event, \'0\', \'menu' . $id . '\')">';
    }
    $color = $menuNode->getData('font_color');
    $fontWeight = $menuNode->getData('font_weight');
    $html[] = '<a class="parentMenu" href="'.$menuNode->getUrl(true).'">';
    $name = $this->escapeHtml($node->getName());
    if($hasIcon) {
      $html[] = '<img src="'.$menuNode->getImageUrl().'"/><span style="color:#'.$color.'; font-weight:'.$fontWeight.'">' . $name . '</span>';
    } elseif($hasImage) {
      $html[] = '<span><img src="'.$menuNode->getImageUrl().'"/></span>';
    } elseif($hasBackgroundImage || $hasBackground) {
      $style = 'background-image:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : '').';background-position:'.($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : '').';background-repeat:'.($this->getImageAsBackground() && $hasImage ? 'no-repeat' : '').';color:' .'#' . $color .'; font-weight:'.$fontWeight;
      $html[] = '<span style="'.$style.'">'.$name.'</span>';
    } else {
      $html[] = '<span style="color:#'.$color.'; font-weight:'.$fontWeight.'">'.$name.'</span>';
    }
    $html[] = '</a>';
    if ($drawPopup)
    {
      $html[] = '<div id="popup' . $id . '" class="explodedmenu-menu-popup" onmouseout="hideMenuPopup(this, event, \'popup' . $id . '\', \'menu' . $id . '\')">';
      if (count($activeChildren))
      {
        $html[] = '<div class="exploded-menu-block">';
        $html[] = $this->drawColumns($activeChildren);
        $html[] = '</div>';
      }
      if ($blockHtml)
      {
        $html[] = '<div class="exploded-cms-block static-block">';
        $html[] = $blockHtml;
        $html[] = '</div>';
      }
      $html[] = '</div>';
    }
    $html[] = '</li>';
    $html = implode("\n", $html);
    return $html;
  }

  public function drawColumns($children, $responsive = false)
  {

    $html = '';
    $columns = (int)$this->getPopupColumn();

    if ($columns < 1) $columns = 1;
    $chunks = $this->explodeByColumns($children, $columns);

    $lastColumnNumber = count($chunks);
    $i = 1;
    foreach ($chunks as $key => $value)
    {
      if (!count($value)) continue;
      $class = '';
      if ($i == 1) $class.= ' first';
      if ($i == $lastColumnNumber) $class.= ' last';
      if ($i % 2) $class.= ' odd'; else $class.= ' even';
      if($responsive)
        $html.= '<ul class="dropdown-menu ' . $class . '">';
      else
        $html.= '<ul class="column' . $class . '">';
      $html.= $this->drawMenuItem($value, 1, $responsive);
      $html.= '</ul>';
      $i++;
    }
    return $html;
  }

  public function getColCount($children){
    $columns = (int)$this->getPopupColumn();

    if ($columns < 1) $columns = 1;
    $chunks = $this->explodeByColumns($children, $columns);
    return count($chunks);
  }

  public function drawMenuItem($children, $level = 1, $responsive = false)
  {
    $html = '';
    foreach ($children as $key => $node)
    {

      $menuNode = $this->getModel($node->getData());

      // --- class for active category ---
      $active = '';
      // --- format category name ---
      $name = $this->escapeHtml($node->getName());
      $hasImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_IMAGE == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
      $hasBackgroundImage = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
      $hasBackground = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_BACKGROUND == $menuNode->getData('display_mode'));
      $hasIcon = (OnlineBiz_MageMenu_Model_Menuitem_Source_Display::DISPLAY_ICON == $menuNode->getData('display_mode') && $menuNode->getImageUrl());
      if (Mage::getStoreConfig('explodedmenu/general/non_breaking_space'))
        $name = str_replace(' ', '&nbsp;', $name);
      if($responsive)
        $html.= '<li class="dropdown parent level' . $level . '"><a class="itemMenuName level' . $level . $active . '" href="' . $menuNode->getUrl(true) . '">';
      else
        $html.= '<li class="itemMenu level' . $menuNode->getData('custom_css_class') . $level . '"><a class="itemMenuName level' . $level . $active . '" href="' . $menuNode->getUrl(true) . '">';
      if($hasIcon) {
        $html.= '<img src="'.$menuNode->getImageUrl().'"/><span>' . $name . '</span>';
      } elseif($hasImage) {
        $html.= '<span><img src="'.$menuNode->getImageUrl().'"/></span>';
      } elseif($hasBackgroundImage || $hasBackground) {
        $style = 'background-image:' .($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? "url('{$menuNode->getImageUrl()}')" : '').';background-position:' .($hasBackgroundImage || ($this->getImageAsBackground() && $hasImage) ? 'center center' : '').';background-repeat:' .($hasBackgroundImage || $this->getImageAsBackground() && $hasImage ? 'no-repeat' : '');
        $html.= '<span style="'.$style.'">'.$name.'</span>';
      } else {
        $html.= '<span>'.$name.'</span>';
      }
      $activeChildren = array();
      $activeTreeChildren = $node->getChildren();
      foreach($activeTreeChildren as $child)
        $activeChildren[] = $child;
      if($responsive && count($activeChildren))
        $html.= '</a><span class="icon-plus expand"></span>';
      else
        $html.= '</a></li>';
      if (count($activeChildren) > 0)
      {
        if($responsive)
          $html.= '<ul class="dropdown-menu level' . $level . '">';
        else
          $html.= '<ul class="itemSubMenu level' . $level . '">';
        $html.= $this->drawMenuItem($activeChildren, $level + 1);
        $html.= '</ul>';
      }
      if($responsive && count($activeChildren))
        $html.= '</li>';
    }
    return $html;
  }

  private function explodeByColumns($target, $num)
  {
    $count = count($target);

    if ($count) $target = array_chunk($target, ceil($count / $num));

    $target = array_pad($target, $num, array());
    return $target;
  }

  private function _countChild($children, $level, &$count)
  {
    foreach ($children as $child)
    {
      if ($this->isCategoryActive($child))
      {
        $count++; $activeChildren = $child->getChildren();
        if (count($activeChildren) > 0) $this->_countChild($activeChildren, $level + 1, $count);
      }
    }
  }
  /**
   *
   *
   * @return OnlineBiz_MageMenu_Model_Menuitem
   */
  public function getCurrentCategory()
  {
    if( ! Mage::registry('magemenu_current_node')) {
      $linkId = false;
      if(Mage::registry('current_category')) {
        $linkType = 'category';
        $linkId = Mage::registry('current_category')->getId();
      }

      if( ! $linkId && Mage::registry('current_product')) {
        $linkType = 'product';
        $linkId = Mage::registry('current_product')->getSku();
      }

      if( ! $linkId && Mage::app()->getRequest()->getParam('page_id')) {
        $linkType = 'cms_page';
        $linkId = Mage::app()->getRequest()->getParam('page_id');
      }

      $nodes = Mage::getModel('magemenu/menuitem')->getCollection()
      ->addFieldToFilter('link_to', $linkType)
      ->addFieldToFilter('link_to_' . $linkType, $linkId);
      $path = array();
      foreach($nodes as $node) {
        $path = array_merge($path, implode('/', $node->getData('path')));
      }
      Mage::register('magemenu_current_node', 1);
    }
    return false;
    if (Mage::getSingleton('catalog/layer')) {
      return Mage::getSingleton('catalog/layer')->getCurrentCategory();
    }
    return false;
  }

  /**
   *
   *
   * @return string
   */
  public function getCurrentCategoryPath()
  {
    if ($this->getCurrentCategory()) {
      return explode(',', $this->getCurrentCategory()->getPathInStore());
    }
    return array();
  }

  /**
   *
   *
   * @param OnlineBiz_MageMenu_Model_Menuitem $menuitem
   * @return string
   */
  public function drawOpenCategoryItem($menuitem) {
    $html = '';
    if (!$menuitem->getIsActive()) {
      return $html;
    }

    $html.= '<li';

    if ($this->isCategoryActive($menuitem)) {
      $html.= ' class="active"';
    }

    $html.= '>'."\n";
    $html.= '<a href="'.$this->getMenuitemUrl($menuitem).'"><span>'.$this->htmlEscape($menuitem->getName()).'</span></a>'."\n";

    if (in_array($menuitem->getId(), $this->getCurrentCategoryPath())){
      $children = $menuitem->getChildren();
      $hasChildren = $children && $children->count();

      if ($hasChildren) {
        $htmlChildren = '';
        foreach ($children as $child) {
          $htmlChildren.= $this->drawOpenCategoryItem($child);
        }

        if (!empty($htmlChildren)) {
          $html.= '<ul>'."\n"
            .$htmlChildren
            .'</ul>';
        }
      }
    }
    $html.= '</li>'."\n";
    return $html;
  }

  public function getTemplate()
  {
    if(is_null(parent::getTemplate())) {
      $this->setTemplate($this->getData('custom_template') ? $this->getData('custom_template') : $this->getData('template'));
    }
    return parent::getTemplate();
  }

  public function _beforeToHtml()
  {
    if( ! $this->hasInstance()) {
      $this->setInstance($this->getInstance());
    }
  }

  public function getCss($default = null)
  {
    if(is_null($default)) {
      return '';
    }

    if(is_array($default)) {
      return implode(' ', $default);
    }
    return $default;
  }
  /**
   * I need an array with the index being continunig numbers, so
   * it's possible to check for the previous/next category
   *
   * @param mixed $collection
   * @return array
   */
  public function toLinearArray($collection)
  {
    $array = array();
    foreach ($collection as $item){
      $array[] = $item;
    }
    return $array;
  }
}