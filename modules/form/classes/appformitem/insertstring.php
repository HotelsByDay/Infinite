<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek slouzi k vlozeni hodnoty pouze pri vytvareni noveho zaznamu.
 * U existujicich zaznamu je hodnota jiz pouze ke cteni a neni ji mozne zmenit.
 *
 * Dedi od AppFormItemString a jeho jedinou specialitou je ze pokud ORM model
 * je jiz ulozen tak neumoznuje editaci hodnoty.
 *
 * Tento prvek se vyuziva napriklad na atributu 'username' modelu 'user', kde
 * je povoleno nastaveni uzivatelskeho jmena pouze pri vytvareni noveho zaznamu
 * a pak uz ne.
 */
class AppFormItem_InsertString extends AppFormItem_String
{
    /**
     * Dodatecne osetreni proti zmene hodnoty u existujicich zaznamu.
     * @param <type> $value
     */
    public function setValue($value)
    {
        if ($this->model->loaded())
        {
            return;
        }

        $this->model->{$this->attr} = $value;
    }

    public function Render($render_style = NULL, $error_message = NULL)
    {
        //u existujicich zaznamu neni mozne editovat hodnotu
        $render_style = $this->model->loaded()
                            ? AppForm::RENDER_STYLE_READONLY
                            : NULL;

        return parent::Render($render_style, $error_message);
    }


}