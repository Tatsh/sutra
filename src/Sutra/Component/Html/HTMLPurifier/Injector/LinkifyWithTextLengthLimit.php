<?php
/**
 * Injector that converts http, https and ftp text URLs to actual links.
 *
 * This differs from the original in that several options are used:
 *   - `AutoFormat.LinkifyWithTextLengthLimit.Limit` - integer|null, string
 *        length limit for text between `<a>` tags
 *   - `AutoFormat.LinkifyWithTextLengthLimit.Suffix` - string|null, string to
 *       be used at the end of text. If the length can fit in the length, no
 *       suffix is added.
 *   - `AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol` - boolean, remove
 *         protocol on displayed text
 */
class HTMLPurifier_Injector_LinkifyWithTextLengthLimit extends HTMLPurifier_Injector
{
    /**
     * Unique name for this.
     *
     * @var string
     */
    public $name = 'SutraLinkify';

    /**
     * Criteria.
     *
     * @var array
     */
    public $needed = array('a' => array('href'));

    /**
     * Link text length limit.
     *
     * @var integer|null
     */
    protected $linkTextLength;

    /**
     * Link text suffix for if a link is too long.
     *
     * @var string|null
     */
    protected $linkTextSuffix;

    /**
     * Remove protocol from displayed link.
     *
     * @var boolean
     */
    protected $linkTextRemoveProtocol;

    /**
     * @param HTMLPurifier_Config  $config  Configuration.
     * @param HTMLPurifier_Context $context Context.
     */
    public function prepare($config, $context)
    {
        parent::prepare($config, $context);
        $this->linkTextLength = $config->get('AutoFormat.LinkifyWithTextLengthLimit.Limit');
        $this->linkTextSuffix = $config->get('AutoFormat.LinkifyWithTextLengthLimit.Suffix');
        $this->linkTextRemoveProtocol = $config->get('AutoFormat.LinkifyWithTextLengthLimit.RemoveProtocol');

        if (!$this->linkTextLength) {
            $this->linkTextLength = null;
        }
    }

    /**
     * Handles parsed token.
     *
     * @param HTMLPurifier_Token &$token Token.
     */
    public function handleText(&$token)
    {
        if (!$this->allowsElement('a')) {
            return;
        }

        $pieces = preg_split('/\s+/', $token->data);
        $token = array();
        $linkTextSuffix = $this->linkTextSuffix ? (string) $this->linkTextSuffix : '';
        $protocols = array(
            'http://',
            'https://',
            'ftp://',
            'mailto:',
        );

        foreach ($pieces as $piece) {
            if ($piece === '') {
                continue;
            }

            $isEmail = (bool) filter_var($piece, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
            $isUri = filter_var($piece, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) && preg_match('/https?|ftp/', $piece);
            $hasProtocol = true;

            if (!$isUri && !$isEmail) {
                // Validate without a scheme, block anything that might have a scheme like ssh://
                $isUri = (bool) preg_match('/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/', $piece) && strpos($piece, ':') === false;

                // Assume http if it matches
                if ($isUri) {
                    $piece = 'http://'.$piece;
                }
            }

            if ($isEmail) {
                $url = $urlText = $piece;
                if (strpos($piece, 'mailto:') === false) {
                    $url = 'mailto:'.$piece;
                }

                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $url));

                if ($this->linkTextRemoveProtocol) {
                    $urlText = str_replace($protocols, '', $urlText);
                }

                if ($this->linkTextLength !== null && strlen($urlText) >= $this->linkTextLength) {
                    $urlText = substr($urlText, 0, $this->linkTextLength).$linkTextSuffix;
                }

                $token[] = new HTMLPurifier_Token_Text($urlText);
                $token[] = new HTMLPurifier_Token_End('a');
            }
            else if ($isUri) {
                $urlText = $piece;

                if ($this->linkTextRemoveProtocol) {
                    $urlText = str_replace($protocols, '', $urlText);
                }

                if ($this->linkTextLength !== null && strlen($urlText) >= $this->linkTextLength) {
                    $urlText = substr($urlText, 0, $this->linkTextLength).$linkTextSuffix;
                }

                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $piece));
                $token[] = new HTMLPurifier_Token_Text($urlText);
                $token[] = new HTMLPurifier_Token_End('a');
            }
            else {
                $token[] = new HTMLPurifier_Token_Text($piece);
            }
        }
    }
}
