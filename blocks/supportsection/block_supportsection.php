<?php
class block_supportsection extends block_base {
    public function init() {
        $this->title = get_string('supportsection', 'block_supportsection');
    }
    public function get_support_items(){
		$this->supportrssurl = "http://esu-local.example.com/support/moodle-support-section";
		$this->supportxml = simplexml_load_file($this->supportrssurlrssurl);

		return $this->supportxml;
	}
    public function get_content() {
	    if ($this->content !== null) {
	      return $this->content;
	    }
	 	$this->content         =  new stdClass;
	    $this->content->text   = '';
	    $this->content->footer = '';
	  	$renderer = $this->page->get_renderer('block_supportsection');
	  	$this->content->text .= $renderer->display_search();
	    return $this->content;
	}	
}