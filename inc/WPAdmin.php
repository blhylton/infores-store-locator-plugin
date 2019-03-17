<?php
/**
 * Created by PhpStorm.
 * User: Barry Hylton
 * Date: 3/17/2019
 * Time: 12:49 AM
 */

namespace BLHylton\InfoResStoreLocator\WordPress;


class WPAdmin extends AbstractUsesTwig
{
    public function init()
    {
        add_action('admin_menu', array($this, 'addConfigurationPage'));
        add_action('admin_init', array($this, 'registerConfigurationSettings'));
        add_action('admin_init', array($this, 'addParameterSettingsSection'));
        add_action('admin_init', array($this, 'addParameterFields'));
    }

    public function addConfigurationPage()
    {
        add_options_page(
            'InfoRes Store Locator Configuration',
            'InfoRes Store Locator Configuration',
            'manage_options',
            'blhirsl-options',
            array($this, 'renderOptionsPage')
        );
    }

    public function registerConfigurationSettings()
    {
        register_setting(
            'blhirsl',
            'blhirsl-configuration'
        );
    }

    public function addParameterSettingsSection()
    {
        add_settings_section(
            'blhirsl-parameters',
            'Request Parameters',
            array($this, 'parameterSettingsIntroText'),
            'blhirsl'
        );
    }

    public function addParameterFields()
    {
        add_settings_field(
            'blhirsl-client-id',
            'Client ID',
            array($this, 'renderInputField'),
            'blhirsl',
            'blhirsl-parameters',
            array(
                'uid' => 'blhirsl-client-id',
                'type' => 'text',
                'helper' => '"clientid" in the URL'
            )
        );

        add_settings_field(
            'blhirsl-product-family-id',
            'Product Family ID',
            array($this, 'renderInputField'),
            'blhirsl',
            'blhirsl-parameters',
            array(
                'uid' => 'blhirsl-product-family-id',
                'type' => 'text',
                'helper' => '"productfamilyid" in the URL'
            )
        );

        add_settings_field(
            'blhirsl-template',
            'Template',
            array($this, 'renderInputField'),
            'blhirsl',
            'blhirsl-parameters',
            array(
                'uid' => 'blhirsl-template',
                'type' => 'text',
                'helper' => '"template" in the URL'
            )
        );

        add_settings_field(
            'blhirsl-product-type',
            'Product Type',
            array($this, 'renderInputField'),
            'blhirsl',
            'blhirsl-parameters',
            array(
                'uid' => 'blhirsl-product-type',
                'type' => 'text',
                'helper' => '"producttype" in the URL'
            )
        );
    }

    public function parameterSettingsIntroText()
    {
        echo 'These settings are specific to your InfoRes installation and can typically be found in the URL to your ' .
            'default store locator';
    }

    public function renderInputField($args)
    {
        $value = get_option($args['uid']);

        if (!$value) {
            $value = '';
        }

        echo $this->render('inputField.fragment.twig.html', array(
            'id' => $args['uid'],
            'value' => $value,
            'type' => $args['type'],
            'helper' => $args['helper']
        ));
    }

    public function renderOptionsPage()
    {
        echo '<form action="options.php" method="post">
    <h2>InfoRes Store Locator Configuration</h2>';
        settings_fields('blhirsl');
        do_settings_sections('blhirsl');
        submit_button();
        echo '</form>';

    }
}