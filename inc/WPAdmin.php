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

        // Parameter settings
        add_action('admin_init', array($this, 'addParameterSettingsSection'));
        add_action('admin_init', array($this, 'addParameterFields'));

        // Post type settings
        add_action('admin_init', array($this, 'addPostTypeSettingsSection'));
        add_action('admin_init', array($this, 'addPostTypeFields'));
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

    public function addPostTypeSettingsSection()
    {
        add_settings_section(
            'blhirsl-post-types',
            'Searchable Post Types',
            array($this, 'postTypeSettingsIntroText'),
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

    public function addPostTypeFields()
    {
        $postTypes = get_post_types(['public' => true], 'object');

        add_settings_field(
            'blhirsl-post-type-selection',
            'Post Types',
            array($this, 'renderSelectField'),
            'blhirsl',
            'blhirsl-post-types',
            array(
                'uid' => 'blhirsl-post-type-selection',
                'helper' => 'Post Types that will be searchable',
                'supplemental' => 'Whichever post type you select here will have a text area added to their content ' .
                    'admin in order for you to associate an ID',
                'postTypes' => array_map(
                    function ($type) {
                        return (object)[
                            "name" => $type->name,
                            "label" => $type->labels->singular_name
                        ];
                    },
                    $postTypes
                )
            )
        );
    }

    public function parameterSettingsIntroText()
    {
        echo 'These settings are specific to your InfoRes installation and can typically be found in the URL to your ' .
            'default store locator';
    }

    public function postTypeSettingsIntroText()
    {
        echo '';
    }

    public function renderInputField($args)
    {
        $value = get_option($args['uid']);

        if (!$value) {
            $value = '';
        }

        echo $this->render('inputField.fragment.twig', array(
            'id' => $args['uid'],
            'value' => $value,
            'type' => $args['type'],
            'helper' => $args['helper']
        ));
    }

    public function renderSelectField($args)
    {
        $value = get_option($args['uid']);

        if (!$value) {
            $value = '';
        }

        echo $this->render('selectField.fragment.twig', array(
            'id' => $args['uid'],
            'value' => $value,
            'helper' => $args['helper'],
            'supplemental' => $args['supplemental'],
            'options' => $args['postTypes']
        ));
    }

    public function renderOptionsPage()
    {
        // Since WordPress likes to echo everything, but we want to use Twig, we have to set up and pass in an output buffer
        ob_start();
        settings_fields('blhirsl');
        do_settings_sections('blhirsl');
        submit_button();
        $content = ob_get_clean();

        echo $this->render('optionsPage.fragment.twig', array(
            'content' => $content,
        ));
    }
}