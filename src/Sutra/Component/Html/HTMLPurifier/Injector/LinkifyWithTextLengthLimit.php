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

        if (strpos($token->data, '://') === false && strpos($token->data, '@') === false) {
            // our really quick heuristic failed, abort
            // this may not work so well if we want to match things like
            // "google.com", but then again, most people don't
            return;
        }

        // there is/are URL(s). Let's split the string:
        // Note: this regex is extremely permissive
        $bits = preg_split('#((?:https?|ftp|mailto):(?://)?[^\s\'",<>()]+)#Su', $token->data, -1, PREG_SPLIT_DELIM_CAPTURE);

        $token = array();
        $linkTextSuffix = $this->linkTextSuffix ? (string) $this->linkTextSuffix : '';
        $protocols = array(
            'http://',
            'https://',
            'ftp://',
            'mailto:',
        );

        if (count($bits) === 1 && strpos($bits[0], '@') !== false) {
            $bits[1] = 'mailto:'.$bits[0];
            $bits[0] = '';
        }

        // $i = index
        // $c = count
        // $l = is link
        for ($i = 0, $c = count($bits), $l = false; $i < $c; $i++, $l = !$l) {
            if (!$l) {
                if ($bits[$i] === '') {
                    continue;
                }

                $token[] = new HTMLPurifier_Token_Text($bits[$i]);
            }
            else {
                $token[] = new HTMLPurifier_Token_Start('a', array('href' => $bits[$i]));
                $urlText = $bits[$i];

                if ($this->linkTextRemoveProtocol) {
                    $urlText = str_replace($protocols, '', $urlText);
                }

                if ($this->linkTextLength !== null && strlen($urlText) >= $this->linkTextLength) {
                    $urlText = substr($urlText, 0, $this->linkTextLength).$linkTextSuffix;
                }

                $token[] = new HTMLPurifier_Token_Text($urlText);
                $token[] = new HTMLPurifier_Token_End('a');
            }
        }
    }
}
