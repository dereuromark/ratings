<?php
/**
 * Copyright 2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Ratings\View\Helper;

use Cake\View\Helper;
use Cake\Utility\Text;

/**
 * CakePHP Ratings Plugin
 *
 * Rating helper
 */
class RatingHelper extends Helper {

/**
 * helpers variable
 *
 * @var array
 */
	public $helpers = array ('Html', 'Form');

/**
 * Allowed types of html list elements
 *
 * @var array $allowedTypes
 */
	public $allowedTypes = array('ul', 'ol', 'radio');

/**
 * Default settings
 *
 * @var array $defaults
 */
	public $defaults = array(
		'stars' => 5,
		'item' => null,
		'value' => 0,
		'type' => 'ul',
		'createForm' => false,
		'url' => array(),
		'link' => true,
		'redirect' => true,
		'class' => 'rating',
		'js' => false,
	);

/**
 * Sizes for rating image
 *
 * @var array
 */
	public $sizes = array('large' => 28, 'medium' => 16, 'small' => 12);

/**
 * Rounds the value according to the steps used (between min/max inclusivly)
 *
 * @param int $value
 * @param int $steps
 * @param int $min
 * @param int $max
 * @return int Value
 */
	public function round($value, $steps = 4, $min = 1, $max = 5) {
		if ($value <= $min) {
			return $min;
		}
		$v = round($value * $steps) / $steps;
		return min($v, $max);
	}

/**
 * @param float $value (0...X)
 * @param array $options
 * - stars (defaults to 5)
 * - steps per image (defaults to 4 => 1/4 accuracy)
 * - ...
 * @param array $attributes for div container (id, style, ...)
 * @return string $divContainer with rating images
 */
	public function ratingImage($value, array $options = array(), array $attr = array()) {
		$size = !empty($options['size']) ? $options['size'] : '';
		if (!empty($size)) {
			$options['pixels'] = $this->sizes[$size];
		}
		$pixels = !empty($options['pixels']) ? $options['pixels'] : 16;
		$steps = !empty($options['steps']) ? $options['steps'] : 4;
		$stars = !empty($options['stars']) ? $options['stars'] : 5;
		if ($value <= 0) {
			$roundedValue = 0;
			if (empty($attr['title'])) {
				$attr['title'] = __d('ratings', 'No rating available yet.');
			}
		} else {
			$roundedValue = $this->round($value, $steps, 1, $stars);
		}

		$array = array(
			0 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-on" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
			1 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-disabled" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
		);

		$res = '';
		$disable = 0;
		for ($i = 0; $i < $stars; $i++ ) {
			for ($j = 0; $j < $steps; $j++) {
				if (!$disable && ($i + $j * (1 / $steps)) >= $roundedValue) {
					$disable = 1;
				}
				$v = $array[$disable];

				if ($j === 0) {
					# try to use a single image if possible
					if ($i < floor($roundedValue) || $i >= ceil($roundedValue) || $i === 0 && $roundedValue >= 1) {
						$res .= Text::insert($v, array('margin' => 0, 'width' => $pixels), array('before' => '{', 'after' => '}'));
						break;
					}
				}

				$margin = 0 - ($pixels / $steps) * $j;
				$res .= Text::insert($v, array('margin' => $margin, 'width' => $pixels / $steps), array('before' => '{', 'after' => '}'));
			}
		}

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$defaults = array(
			'title' => __d('ratings', '{0} of {1} stars', number_format($roundedValue, $precision, ',', '.'), $stars),
		);
		$attr = array_merge($defaults, $attr);
		return $this->Html->div('ratingStars clearfix', $res, $attr);
	}

/**
 * @param float $value (0...X)
 * @param array $options
 * - stars (defaults to 5)
 * - steps per image (defaults to 4 => 1/4 accuracy)
 * - ...
 * @param array $attributes for div container (id, style, ...)
 * @return string $divContainer with rating images
 */
	public function image($value, array $options = array(), array $attr = array()) {
		$defaults = [
			//'type' => 'bootstrap',
			'data-symbol' => '&#xf005;',
			'escape' => false,
			'data-rating-class' => 'rating-fa',
			'stars' => 5,
			'steps' => 4,
		];
		$options += $defaults;

		if ($value <= 0) {
			$roundedValue = 0;
		} else {
			$roundedValue = round($value * $options['steps']) / $options['steps'];
		}
		$percent = $this->percentage($roundedValue, $options['stars']);

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$title = __d('ratings', '{0} of {1} stars', number_format(min($roundedValue, $options['stars']), $precision, ',', '.'), $options['stars']);

		$attrContent = [
			'class' => 'rating-stars', 'data-content' => str_repeat($options['data-symbol'], $options['stars']), 'escape' => $options['escape'],
			'style' => 'width: ' . $percent . '%'
		];
		$content = $this->Html->div(null, '', $attrContent);

		//<div class="rating-container rating-fa" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" title="x of y stars">
		//	<div class="rating-stars" data-content="&#xf005;&#xf005;&#xf005;&#xf005;&#xf005;" style="width: 20%;"></div>
		//</div>

		$attr += ['title' => $title];
		$attr = ['data-content' => str_repeat($options['data-symbol'], $options['stars']), 'escape' => $options['escape']] + $attr;
		return $this->Html->div('rating-container ' . $options['data-rating-class'], $content, $attr);


		$size = !empty($options['size']) ? $options['size'] : '';
		if (!empty($size)) {
			$options['pixels'] = $this->sizes[$size];
		}
		$pixels = !empty($options['pixels']) ? $options['pixels'] : 16;
		$steps = !empty($options['steps']) ? $options['steps'] : 4;
		if ($value <= 0) {
			$roundedValue = 0;
		} else {
			$roundedValue = round($value * $steps) / $steps;
		}
		$stars = !empty($options['stars']) ? $options['stars'] : 5;

		$array = array(
			0 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-on" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
			1 => '<div class="ui-stars-star' . ($size ? '-' . $size : '') . ' ui-stars-star' . ($size ? '-' . $size : '') . '-disabled" style="cursor: default; width: {width}px;"><a style="margin-left: {margin}px;">#</a></div>',
		);

		$res = '';
		$disable = 0;
		for ($i = 0; $i < $stars; $i++ ) {
			for ($j = 0; $j < $steps; $j++) {
				if (!$disable && ($i + $j * (1 / $steps)) >= $roundedValue) {
					$disable = 1;
				}
				$v = $array[$disable];

				if ($j === 0) {
					# try to use a single image if possible
					if ($i < floor($roundedValue) || $i >= ceil($roundedValue) || $i === 0 && $roundedValue >= 1) {
						$res .= Text::insert($v, array('margin' => 0, 'width' => $pixels), array('before' => '{', 'after' => '}'));
						break;
					}
				}

				$margin = 0 - ($pixels / $steps) * $j;
				$res .= Text::insert($v, array('margin' => $margin, 'width' => $pixels / $steps), array('before' => '{', 'after' => '}'));
			}
		}

		$precision = 2;
		if ((int)$roundedValue == $roundedValue) {
			$precision = 0;
		} elseif ((int)(2 * $roundedValue) == 2 * $roundedValue) {
			$precision = 1;
		}
		$defaults = array(
			'title' => number_format(min($roundedValue, $stars), $precision, ',', '.') . ' ' . __('von') . ' ' . $stars . ' ' . __('Sternen'),
		);
		$attr = array_merge($defaults, $attr);
		return $this->Html->div('ratingStars clearfix', $res, $attr);
	}

	/**
	 * @param array $options
	 * @param array $htmlAttributes
	 * @return string HTML
	 */
	public function input($field, array $options = array(), array $htmlAttributes = array()) {

		$htmlAttributes += $options;
		return $this->Form->input($field, $htmlAttributes);
	}

/**
 * Displays a bunch of rating links wrapped into a list element of your choice
 *
 * @param array $options
 * @param array $htmlAttributes Attributes for the rating links inside the list
 * @return string markup that displays the rating options
 */
	public function display(array $options = array(), array $htmlAttributes = array()) {
		$options += $this->defaults;
		if (empty($options['item'])) {
			throw new \Exception(__d('ratings', 'You must set the id of the item you want to rate.'), E_USER_NOTICE);
		}

		if ($options['type'] === 'bootstrap') {
			return $this->input($options, $htmlAttributes);
		}

		if ($options['type'] === 'radio' || $options['type'] === 'select') {
			return $this->starForm($options, $htmlAttributes);
		}

		$stars = null;
		for ($i = 1; $i <= $options['stars']; $i++) {
			$link = null;
			if ($options['link']) {
				$url = $options['url'];
				if (!isset($url['?'])) {
					$url['?'] = array();
				}
				$url['?'] = array_merge($url['?'], array('rate' => $options['item'], 'rating' => $i));
				if ($options['redirect']) {
					$url['?']['redirect'] = 1;
				}

				$link = $this->Html->link($i, $url, $htmlAttributes);
			}
			$stars .= $this->Html->tag('li', $link, array('class' => 'star' . $i));
		}

		if (in_array($options['type'], $this->allowedTypes)) {
			$type = $options['type'];
		} else {
			$type = 'ul';
		}

		$stars = $this->Html->tag($type, $stars, array('class' => $options['class'] . ' ' . 'rating-' . round($options['value'], 0)));
		return $stars;
	}

/**
 * Bar rating
 *
 * @param integer value
 * @param integer total amount of rates
 * @param array options
 * @return string
 */
	public function bar($value, $total, array $options = array()) {
		$defaultOptions = array(
			'innerClass' => 'inner',
			'innerHtml' => '<span>%value%</span>',
			'innerOptions' => array(),
			'outerClass' => 'barRating',
			'outerOptions' => array(),
			'element' => null);
		$options += $defaultOptions;

		$percentage = $this->percentage($value, $total);

		if (!empty($options['element'])) {
			return $this->_View->element($options['element'], array(
				'value' => $value,
				'percentage' => $percentage,
				'total' => $total));
		}

		$options['innerOptions']['style'] = 'width: ' . $percentage . '%';
		$innerContent = str_replace('%value%', $value, $options['innerHtml']);
		$innerContent = str_replace('%percentage%', $percentage, $innerContent);
		$inner = $this->Html->div($options['innerClass'], $innerContent, $options['innerOptions']);

		return $this->Html->div($options['outerClass'], $inner, $options['outerOptions']);
	}

/**
 * Calculates the percentage value
 *
 * @param integer value
 * @param integer total amount
 * @param integer precision of rounding
 * @return mixed float or integer based on the precision value
 */
	public function percentage($value, $total, $precision = 2) {
		if ($total > 0) {
			return (round($value / $total, $precision) * 100);
		}
		return 0;
	}

/**
 * Displays a star form
 *
 * @param array $options
 * @param array $htmlAttributes Attributes for the rating links inside the list
 * @return string markup that displays the rating options
 */
	public function starForm($options = array(), $htmlAttributes = array()) {
		$options += $this->defaults;
		$flush = false;
		if (empty($options['item'])) {
			trigger_error(__d('ratings', 'You must set the id of the item you want to rate.'), E_USER_NOTICE);
		}
		$id = $options['item'];

		$result = '';
		if ($options['createForm']) {
			$result .= $this->Form->create(null, $options['createForm']) . "\n";
		}
		$inputField = 'rating';
		if (!empty($options['inputField'])) {
			$inputField = $options['inputField'];
		}

		$inputOptions = array(
			'type' => in_array($options['type'], array('radio', 'select')) ? $options['type'] : 'radio',
			'id' => 'starelement_' . $id,
			'value' => isset($options['value']) ? round($options['value']) : 0,
			'options' => array_combine(range(1, $options['stars']), range(1, $options['stars']))
		);
		if ($options['js']) {
			$inputOptions['type'] = 'select';
		}
		$inputOptions += $htmlAttributes;

		$result .= '<div id="star_' . $id . '" class="' . (!empty($options['class']) ? $options['class'] : 'rating') . '">';
		$result .= $this->Form->input($inputField, $inputOptions);
		$result .= '</div>';
		if ($options['createForm']) {
			if (!empty($options['target']) && !empty($options['createForm']['url']) && !empty($options['createForm']['ajaxOptions'])) {
				$result .= $this->Js->submit(__d('ratings', 'Rate!'), array_merge(array('url' => $options['createForm']['url']), $options['createForm']['ajaxOptions'])) . "\n";
				$flush = true;
			} else {
				$result .= $this->Form->submit(__d('ratings', 'Rate!')) . "\n";
			}
			$result .= $this->Form->end() . "\n";

			$disabled = empty($editable) ? false : 'disabled';
			if ($disabled) {
				$split = 4;
			} else {
				$split = 1;
			}
			if ($options['js']) {
			/*
			$this->Js->buffer('
			$("#star_' . $id . '").stars({
				cancelValue: 0,
				inputType: "select",
				split:' . $split . ',
				cancelShow: true,
					'.(!$disabled ? 'captionEl: $("#cap_' . $id . '"),' : '').'
					callback: function(ui, type, value) {
						' . (isset($callback) ? $callback . '("star_' . $id . '");' : '') . '
					}
			}); eval();
	');
		*/
		$result .= '<script>$(document).ready(function () {
$("#star_' . $id . '").stars({
	cancelValue: 0,
	inputType: "' . $inputOptions['type'] . '",
	split:1,
	cancelShow: true,
	captionEl: $("#cap_' . $id . '"),
	callback: function(ui, type, value) {
	' . (isset($options['callback']) ? ($options['callback'] . PHP_EOL) : '') . '}
	});
});</script>';
			}

			if ($flush) {
				$this->Js->writeBuffer();
			}
		}
		return $result;
	}
}
