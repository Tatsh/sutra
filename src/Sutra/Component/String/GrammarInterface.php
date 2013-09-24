<?php
namespace Sutra\Component\String;

interface GrammarInterface
{
    public function camelize($str);
    public function dashize($str);
    public function pluralize($word);
    public function singularize($word);
    public function studlyize($str);
    public function underscorize($str);
    public function humanize($str);
    public function titleize($str);

    public function addCamelizationRule($word, $replacement);
    public function addDashizationRule($word, $replacement);
    public function addPluralizationRule($word, $replacement);
    public function addSingularizationRule($word, $replacement);
    public function addStudlyizationRule($word, $replacement);
    public function addUnderscorizationRule($word, $replacement);
    public function addHumanizationRule($substr, $replacement);
    public function addTitleizationRule($substr, $replacement);
    public function removeCamelizationRule($word);
    public function removeDashizationRule($word);
    public function removePluralizationRule($word);
    public function removeSingularizationRule($word);
    public function removeStudlyizationRule($word);
    public function removeUnderscorizationRule($word);
    public function removeHumanizationRule($substr);
    public function removeTitleizationRule($substr);
}
