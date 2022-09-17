<?php

namespace MediaWiki\Extension\Z17;

use OutputPage, Parser, PPFrame, Skin;

/**
 * Class MW_EXT_Align
 */
class MW_EXT_Align
{
  /**
   * Get align.
   *
   * @param $id
   *
   * @return array
   */
  private static function getAlign($id)
  {
    $get = MW_EXT_Kernel::getJSON(__DIR__ . '/storage/align.json');
    $out = $get['align'][$id] ?? [] ?: [];

    return $out;
  }

  /**
   * Get align type.
   *
   * @param $id
   * @param $type
   *
   * @return string
   */
  private static function getType($id, $type)
  {
    $id = self::getAlign($id) ? self::getAlign($id) : '';
    $out = $id[$type] ?? '' ?: '';

    return $out;
  }

  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return bool
   * @throws \MWException
   */
  public static function onParserFirstCallInit(Parser $parser)
  {
    $parser->setHook('align', [__CLASS__, 'onRenderTag']);

    return true;
  }

  /**
   * Render tag function.
   *
   * @param $input
   * @param array $args
   * @param Parser $parser
   * @param PPFrame $frame
   *
   * @return string
   */
  public static function onRenderTag($input, array $args, Parser $parser, PPFrame $frame)
  {
    // Argument: id.
    $getID = MW_EXT_Kernel::outClear($args['id'] ?? '' ?: '');
    $outID = MW_EXT_Kernel::outNormalize($getID);

    // Argument: type.
    $getType = MW_EXT_Kernel::outClear($args['type'] ?? '' ?: '');
    $outType = MW_EXT_Kernel::outNormalize($getType);

    // Check note type, set error category.
    if (!self::getAlign($outID) || !self::getType($outID, $outType)) {
      $parser->addTrackingCategory('mw-ext-align-error-category');

      return null;
    }

    // Get content.
    $getContent = trim($input);
    $outContent = $parser->recursiveTagParse($getContent, $frame);

    // Out HTML.
    $outHTML = '<div class="mw-ext-align mw-ext-align-' . $outID . '-' . $outType . '">' . $outContent . '</div>';

    // Out parser.
    $outParser = $outHTML;

    return $outParser;
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return bool
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin)
  {
    $out->addModuleStyles(['ext.mw.align.styles']);

    return true;
  }
}
