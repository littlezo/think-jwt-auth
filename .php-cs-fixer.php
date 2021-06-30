<?php

$header = <<<'EOF'
	#logic 做事不讲究逻辑，再努力也只是重复犯错
	## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。
	## 只要思想不滑稽，方法总比苦难多！
	@version 1.0.0
	@author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
	@contact  littlezov@qq.com
	@link     https://github.com/littlezo
	@document https://github.com/littlezo/wiki
	@license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE

	EOF;

$finder = PhpCsFixer\Finder::create()
	->files()
	->name('*.php')
	->exclude('public')
	->exclude('runtime')
	->exclude('vendor')
	->in(__DIR__)
	->notName('.php-cs-fixer.*')
	->ignoreUnreadableDirs()
	->ignoreDotFiles(true)
	->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config->setRiskyAllowed(true)
	->setRules([
		'@PHP74Migration' => true,
		'@PHP80Migration' => true,
		'@DoctrineAnnotation' => true,
		'@Symfony' => true,
		'@PSR12' => true,
		'strict_param' => true,
		'array_syntax' => ['syntax' => 'short'],
		'header_comment' => [
			// 'commentType' => 'PHPDoc',
			'header' => $header,
			// 'header' => '',
		],
		'array_syntax' => [
			'syntax' => 'short',
		],
		'binary_operator_spaces' => [
			// 'align_double_arrow' => false,
			// 'align_equals' => false,
		],
		'list_syntax' => [
			'syntax' => 'short',
		],
		'concat_space' => [
			'spacing' => 'one',
		],
		'blank_line_before_statement' => [
			'statements' => [
				'declare',
			],
		],
		'general_phpdoc_annotation_remove' => [
			'annotations' => [
				'date',
			],
		],
		'ordered_imports' => [
			'imports_order' => [
				'class', 'function', 'const',
			],
			'sort_algorithm' => 'alpha',
		],
		'single_line_comment_style' => [
			'comment_types' => [],
		],
		'yoda_style' => [
			'always_move_variable' => false,
			'equal' => false,
			'identical' => false,
		],
		'phpdoc_align' => [
			'align' => 'left',
		],
		'multiline_whitespace_before_semicolons' => [
			'strategy' => 'no_multi_line',
		],
		'no_trailing_comma_in_singleline_array' => true,
		'trailing_comma_in_multiline' => ['after_heredoc' => true],
		'no_trailing_comma_in_list_call' => true,
		'constant_case' => [
			'case' => 'lower',
		],
		'class_attributes_separation' => true,
		'combine_consecutive_unsets' => true,
		'declare_strict_types' => true,
		'linebreak_after_opening_tag' => true,
		'lowercase_static_reference' => true,
		'no_useless_else' => true,
		'no_unused_imports' => true,
		'not_operator_with_successor_space' => true,
		'not_operator_with_space' => false,
		'ordered_class_elements' => true,
		'php_unit_strict' => false,
		'phpdoc_separation' => false,
		'single_quote' => true,
		'standardize_not_equals' => true,
		'multiline_comment_opening_closing' => true,
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
	->setUsingCache(false);
