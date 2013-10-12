<?php
namespace Sutra\Component\String;

/**
 * String manipulation library.
 *
 * @todo Add all `@replaces` annotations.
 */
interface GrammarInterface
{
    /**
     * Converts string to `camelCase`.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function camelize($str);

    /**
     * Adds special rule for `#camelize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addCamelizationRule($word, $replacement);

    /**
     * Removes a run-time camelisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removeCamelizationRule($word);

    /**
     * Converts string to `dash-case`.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function dashize($str);

    /**
     * Adds special rule for `#dashize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addDashizationRule($word, $replacement);

    /**
     * Removes a run-time dashisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removeDashizationRule($word);

    /**
     * Converts a string to humanised form.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function humanize($str);

    /**
     * Adds special rule for `#humanize()` for a substring.
     *
     * @param string $substr      Substring to handle.
     * @param string $replacement Replacement.
     */
    public function addHumanizationRule($substr, $replacement);

    /**
     * Removes a run-time humanisation rule.
     *
     * @param string $substr Substring to handle.
     */
    public function removeHumanizationRule($substr);

    /**
     * Converts a string to `StudlyCase`.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function studlyize($str);

    /**
     * Adds special rule for `#studlyize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addStudlyizationRule($word, $replacement);

    /**
     * Removes a run-time studylisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removeStudlyizationRule($word);

    /**
     * Converts a string to `underscore_case`.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function underscorize($str);

    /**
     * Adds special rule for `#underscorize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addUnderscorizationRule($word, $replacement);

    /**
     * Removes a run-time underscorisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removeUnderscorizationRule($word);

    /**
     * Converts a string to `Title Case`, with handling of special words such
     *   as 'of'.
     *
     * @param string $str String to convert.
     *
     * @return string Converted string.
     */
    public function titleize($str);

    /**
     * Adds special rule for `#titleize()` for a substring.
     *
     * @param string $substr      Substring to handle.
     * @param string $replacement Replacement.
     */
    public function addTitleizationRule($substr, $replacement);

    /**
     * Removes a run-time titleisation rule.
     *
     * @param string $substr Substring to handle.
     */
    public function removeTitleizationRule($substr);

    /**
     * Converts a plural word to singular form.
     *
     * @param string $word String to convert.
     *
     * @return string Converted string.
     */
    public function singularize($word);

    /**
     * Adds special rule for `#singularize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addSingularizationRule($word, $replacement);

    /**
     * Removes a run-time singularisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removeSingularizationRule($word);

    /**
     * Converts a singular word to plural form.
     *
     * @param string $word String to convert.
     *
     * @return string Converted string.
     */
    public function pluralize($word);

    /**
     * Adds special rule for `#pluralize()`.
     *
     * @param string $word        Word to handle.
     * @param string $replacement Replacement.
     */
    public function addPluralizationRule($word, $replacement);

    /**
     * Removes a run-time pluralisation rule.
     *
     * @param string $word Word to handle.
     */
    public function removePluralizationRule($word);

    /**
     * Creates the stem for a word.
     *
     * @param string $word Word to get stem for.
     *
     * @return string Stemmed word.
     *
     * @replaces ::stem
     */
    public function stem($word);

    /**
     * Inflects to singular or plural based on quantity value.
     *
     * @param array|integer $quantity             Quantity or array to count.
     * @param string        $singular             Singular form of word.
     * @param string        $plural               Plural form of word. Use `%d`
     *   to include quantity in returned string.
     * @param boolean       $wordsForSingleDigits Use words for single digits 0-9.
     *
     * @return string Singular or plural form of word or string with plural
     *   form and number.
     *
     * @replaces ::inflectOnQuantity
     */
    public function inflectOnQuantity($quantity, $singular, $plural = null, $wordsForSingleDigits = false);
}
