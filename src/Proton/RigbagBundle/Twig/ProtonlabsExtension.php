<?php
namespace Proton\RigbagBundle\Twig;

class ProtonlabsExtension extends \Twig_Extension {
	
	
	public function getFilters() {
		
		return array (
				'stripslashes'			=> new \Twig_Filter_Method( $this, 'stripslashes' ),
				'tweet'					=> new \Twig_Filter_Method( $this, 'tweet' ),
			);
	}
	
	public function tweet( $value ) {
		
		$value = preg_replace(
				'@(https?://([-\w\.]+)+(/([\w/_\.]*(\?\S+)?(#\S+)?)?)?)@',
				'<a href="$1" target="_blank">$1</a>',
				$value);
		
		$value = preg_replace(
				'/@(\w+)/',
				'<a href="http://twitter.com/$1" target="_blank">@$1</a>',
				$value);
		
		$value = preg_replace(
				'/\s+#(\w+)/',
				' <a href="http://search.twitter.com/search?q=%23$1" target="_blank">#$1</a>',
				$value);
		
		return $value;
	}
	
	
	public function stripslashes( $value ) {
		return stripslashes( $value );
	}
	
	public function getName() {
		
		return 'protonlabs_extension';
	}
}