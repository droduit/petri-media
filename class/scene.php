<?php
class Scene {
	private $sceneArray;
	private $id;
	private $title;
	private $sprites;
	private $childsIds;
	private $container;
	private $position;
	
	function __construct($scene=array()) {
		$this->sceneArray = $scene;
		
		$this->id = $this->sceneArray['id'];	
		$this->title = isset($scene['title']) ? $scene['title'] : $this->id;
		$sprites = array();
		
		$this->childsIds = array();
		$this->container = exist($scene, 'container', 1);
		$this->position = exist($scene, 'position', null);
	}
	
	function getContainer() {
	   return $this->container;   
	}
	
	function getPosition() {
	    return $this->position;
	}
	
	function setPosition($newPos) {
	    $this->position = $newPos;
	}
	
	function setChildsIds($ids) {
	    $this->childsIds = $ids;
	}
	
	function addSprite($sprite) {
	    $sprite->setSceneParent($this);
	    $this->sprites[$sprite->getId()] = $sprite;

	    $childsIds = $sprite->getChildsIds();
	    foreach($childsIds as $ci) {
	       array_push($this->childsIds, $ci);
	    }
	}
	
	function getSprite($spriteId) {
		return (isset($this->sprites[$spriteId])) ? 
		  $this->sprites[$spriteId] : null;
	}
	
	function bindTransition($transition) {
		foreach($transition->getEvents() as $e) {
			if($this->sprites[(string)$e->getElemTrigger()] != null) {
				$this->sprites[(string)$e->getElemTrigger()]->attachEvent($e);
			}
		}
	}
	
	
	function getCSS() {
		global $CSS_STYLES;
		$htmlProp = "<style>";
		
		// Style of the scene
		if(isset($this->sceneArray['style'])) {
			$htmlProp .= "body {";
			foreach($this->sceneArray['style'] as $k => $v) {
				$htmlProp .= $k .":". $v."; ";
			}
			$htmlProp .= "} ";
		}
		
		// Custom class
		if(isset($this->sceneArray['css'])) {
			foreach($this->sceneArray['css'] as $class => $values) {
				$htmlProp .= $class." {";
				foreach($values as $k => $v) {
					$htmlProp .= $k .":". $v."; ";
				}
				$htmlProp .= "} ";
			}
		}
		
		// Styles of sprites
		foreach($this->sprites as $sprite) {
			$props = $sprite->getProps();
			
			if(isset($props['style']) && count($props['style']) == 0)
				return $htmlProp=="" ? "" : $htmlProp."</style>";
			
			foreach($CSS_STYLES as $kindCss) {
				if(isset($props[$kindCss]) && count($props[$kindCss]) > 0) {
					$kind = ($kindCss != "style") ? ":".$kindCss : "";
					$htmlProp.= ".".$sprite->getName().$kind." {";
					foreach($props[$kindCss] as $k => $v) {
						$htmlProp .= $k.":".$v."; ";
					}
					$htmlProp .= "}";
				}
			}
		}
		
		$htmlProp .= "</style>";
		
		return $htmlProp;
	}
	
	function getJS() {
		$scripts = "";
		
		foreach($this->sprites as $sprite) {
			$scripts .= $sprite->getJS();
		}
		
		if(empty($scripts)) return "";
		return "<script>$(function(){ ".$scripts." });</script>";
	}
	
	function getHTMLContent() {
		$html = "";
		$spritesToRender = array_filter($this->sprites, function($s){
		    return !in_array($s->getId(), $this->childsIds);
		});
		
		if(count($spritesToRender) > 0) {
		    foreach($spritesToRender as $sprite) {
				$html .= $sprite->getHTML();
			}
		}
		return $html;
	}
	
	function getArray() { return $this->sceneArray; }
	function getSprites() { return $this->sprites; }
	function getId() { return $this->id; }
	function getTitle() { return $this->title; }
	function debug() { echo "SCENE : ".$this->id.'<hr><pre>'; print_r($this); echo '</pre><hr><br>'; }

	function processSpritesChilds() {
	    foreach($this->sprites as $sprite) {
	        $sprite->setAllChildsIds($sprite->computeAllChildsIds());
	    }
	}
}
?>