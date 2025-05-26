<?php
/**
 * HOMM Icons plugin for Craft CMS 3.x
 *
 * Craft CMS Icon Picker
 *
 * @link      https://github.com/HOMMinteractive
 * @copyright Copyright (c) 2019 HOMM interactive
 */

namespace homm\hommicons\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\Asset;
use craft\helpers\Json;
use homm\hommicons\assetbundles\hommicons\HOMMIconsAsset;
use homm\hommicons\HOMMIcons;
use yii\db\Schema;

/**
 * Class HOMMIconsField
 *
 * @author    Benjamin Ammann
 * @package   HOMMIcons
 * @since     0.0.1
 */
class HOMMIconsField extends Field implements PreviewableFieldInterface
{
    /**
     * @deprecated only exists for backwards compatibility
     */
    public $someAttribute = '';

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('hommicons', 'HOMM Icon Picker');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): array|string
    {
        return Schema::TYPE_STRING;
    }


    /**
     * @inheritdoc
     */
    public function normalizeValue(mixed $value, ?\craft\base\ElementInterface $element = null): mixed
    {
        if (gettype($value) == 'string') {
            if (isset(json_decode($value)->icon)) {
                return json_decode($value)->icon;
            } else {
                return $value;
            }
        } elseif (gettype($value) == 'array') {
            return $value;
        } else {
            return $value;
        }

    }


    /**
     * @inheritdoc
     */
    public function getInputHtml(mixed $value, ?\craft\base\ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(HOMMIconsAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
        ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').HOMMIconsField(" . $jsonVars . ");");

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'hommicons/_components/fields/HOMMIconsField_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'icons' => $this->getIcons()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getPreviewHtml(mixed $value, ElementInterface $element): string
    {
        if (!$value) {
            return '';
        }
        return '<span style="display: flex; gap: 7px; align-items: center;">'
                . '<img src="/' . HOMMIcons::$plugin->getSettings()->iconsVolume . '/icons/' . $value . '.svg" alt="' . $value . '" width="20" height="20" loading="lazy">'
                . '<label><small>' . $value . '</small></label>'
                . '</span>';
    }

    /**
     * Get the Icons of the volume provided by the settings
     *
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    private function getIcons()
    {
        return Asset::find()
            ->volume(HOMMIcons::$plugin->getSettings()->iconsVolume)
            ->all();
    }
}
