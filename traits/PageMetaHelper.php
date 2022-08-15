<?php namespace GemFourMedia\GCompany\Traits;

trait PageMetaHelper {
	
	/**
	 * Set page meta|og tags
	 * ---
	 * @var collection $item
	 */
	protected function setPageMeta($item)
    {
        if (!$item) return;

        // General Meta Tags
        $this->page->title 				= $this->page->meta_title 	  = $this->page->og_title = $item->meta_title;
        $this->page->meta_description 	= $this->page->og_description = $item->meta_description;
        $this->page->meta_keywords 		= $item->meta_keywords;

        // Extra Og Meta Tags
        $this->page->og_image 			= $item->og_image;
        $this->page->og_type 			= $item->og_type;
    }
}