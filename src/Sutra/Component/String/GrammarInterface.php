<?php
namespace Sutra\Component\String;

/**
 * String manipulation library.
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
}
