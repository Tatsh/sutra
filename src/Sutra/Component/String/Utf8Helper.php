<?php
namespace Sutra\Component\String;

/**
 * {@inheritDoc}
 */
class Utf8Helper implements Utf8HelperInterface
{
    /**
     * A mapping of all ASCII-based latin characters, puntuation, symbols and
     *   number forms to ASCII.
     *
     * Includes elements from the following unicode blocks:
     *  - Latin-1 Supplement
     *  - Latin Extended-A
     *  - Latin Extended-B
     *  - IPA Extensions
     *  - Latin Extended Additional
     *  - General Punctuation
     *  - Letterlike symbols
     *  - Number Forms
     *
     * @var array
     */
    protected static $utf8ToAscii = array(
        // Latin-1 Supplement
        '©' => '(c)', '«' => '<<',  '®' => '(R)', '»' => '>>',  '¼' => '1/4',
        '½' => '1/2', '¾' => '3/4', 'À' => 'A',   'Á' => 'A',   'Â' => 'A',
        'Ã' => 'A',   'Ä' => 'A',   'Å' => 'A',   'Æ' => 'AE',  'Ç' => 'C',
        'È' => 'E',   'É' => 'E',   'Ê' => 'E',   'Ë' => 'E',   'Ì' => 'I',
        'Í' => 'I',   'Î' => 'I',   'Ï' => 'I',   'Ñ' => 'N',   'Ò' => 'O',
        'Ó' => 'O',   'Ô' => 'O',   'Õ' => 'O',   'Ö' => 'O',   'Ø' => 'O',
        'Ù' => 'U',   'Ú' => 'U',   'Û' => 'U',   'Ü' => 'U',   'Ý' => 'Y',
        'à' => 'a',   'á' => 'a',   'â' => 'a',   'ã' => 'a',   'ä' => 'a',
        'å' => 'a',   'æ' => 'ae',  'ç' => 'c',   'è' => 'e',   'é' => 'e',
        'ê' => 'e',   'ë' => 'e',   'ì' => 'i',   'í' => 'i',   'î' => 'i',
        'ï' => 'i',   'ñ' => 'n',   'ò' => 'o',   'ó' => 'o',   'ô' => 'o',
        'õ' => 'o',   'ö' => 'o',   'ø' => 'o',   'ù' => 'u',   'ú' => 'u',
        'û' => 'u',   'ü' => 'u',   'ý' => 'y',   'ÿ' => 'y',
        // Latin Extended-A
        'Ā' => 'A',   'ā' => 'a',   'Ă' => 'A',   'ă' => 'a',   'Ą' => 'A',
        'ą' => 'a',   'Ć' => 'C',   'ć' => 'c',   'Ĉ' => 'C',   'ĉ' => 'c',
        'Ċ' => 'C',   'ċ' => 'c',   'Č' => 'C',   'č' => 'c',   'Ď' => 'D',
        'ď' => 'd',   'Đ' => 'D',   'đ' => 'd',   'Ē' => 'E',   'ē' => 'e',
        'Ĕ' => 'E',   'ĕ' => 'e',   'Ė' => 'E',   'ė' => 'e',   'Ę' => 'E',
        'ę' => 'e',   'Ě' => 'E',   'ě' => 'e',   'Ĝ' => 'G',   'ĝ' => 'g',
        'Ğ' => 'G',   'ğ' => 'g',   'Ġ' => 'G',   'ġ' => 'g',   'Ģ' => 'G',
        'ģ' => 'g',   'Ĥ' => 'H',   'ĥ' => 'h',   'Ħ' => 'H',   'ħ' => 'h',
        'Ĩ' => 'I',   'ĩ' => 'i',   'Ī' => 'I',   'ī' => 'i',   'Ĭ' => 'I',
        'ĭ' => 'i',   'Į' => 'I',   'į' => 'i',   'İ' => 'I',   'ı' => 'i',
        'Ĳ' => 'IJ',  'ĳ' => 'ij',  'Ĵ' => 'J',   'ĵ' => 'j',   'Ķ' => 'K',
        'ķ' => 'k',   'Ĺ' => 'L',   'ĺ' => 'l',   'Ļ' => 'L',   'ļ' => 'l',
        'Ľ' => 'L',   'ľ' => 'l',   'Ŀ' => 'L',   'ŀ' => 'l',   'Ł' => 'L',
        'ł' => 'l',   'Ń' => 'N',   'ń' => 'n',   'Ņ' => 'N',   'ņ' => 'n',
        'Ň' => 'N',   'ň' => 'n',   'ŉ' => "'n", 'Ŋ' => 'N',   'ŋ' => 'n',
        'Ō' => 'O',   'ō' => 'o',   'Ŏ' => 'O',   'ŏ' => 'o',   'Ő' => 'O',
        'ő' => 'o',   'Œ' => 'OE',  'œ' => 'oe',  'Ŕ' => 'R',   'ŕ' => 'r',
        'Ŗ' => 'R',   'ŗ' => 'r',   'Ř' => 'R',   'ř' => 'r',   'Ś' => 'S',
        'ś' => 's',   'Ŝ' => 'S',   'ŝ' => 's',   'Ş' => 'S',   'ş' => 's',
        'Š' => 'S',   'š' => 's',   'Ţ' => 'T',   'ţ' => 't',   'Ť' => 'T',
        'ť' => 't',   'Ŧ' => 'T',   'ŧ' => 't',   'Ũ' => 'U',   'ũ' => 'u',
        'Ū' => 'U',   'ū' => 'u',   'Ŭ' => 'U',   'ŭ' => 'u',   'Ů' => 'U',
        'ů' => 'u',   'Ű' => 'U',   'ű' => 'u',   'Ų' => 'U',   'ų' => 'u',
        'Ŵ' => 'W',   'ŵ' => 'w',   'Ŷ' => 'Y',   'ŷ' => 'y',   'Ÿ' => 'Y',
        'Ź' => 'Z',   'ź' => 'z',   'Ż' => 'Z',   'ż' => 'z',   'Ž' => 'Z',
        'ž' => 'z',
        // Latin Extended-B
        'ƀ' => 'b',   'Ɓ' => 'B',   'Ƃ' => 'B',   'ƃ' => 'b',   'Ɔ' => 'O',
        'Ƈ' => 'C',   'ƈ' => 'c',   'Ɖ' => 'D',   'Ɗ' => 'D',   'Ƌ' => 'D',
        'ƌ' => 'd',   'Ǝ' => 'E',   'Ɛ' => 'E',   'Ƒ' => 'F',   'ƒ' => 'f',
        'Ɠ' => 'G',   'Ɨ' => 'I',   'Ƙ' => 'K',   'ƙ' => 'k',   'ƚ' => 'l',
        'Ɯ' => 'M',   'Ɲ' => 'N',   'ƞ' => 'n',   'Ɵ' => 'O',   'Ơ' => 'O',
        'ơ' => 'o',   'Ƣ' => 'OI',  'ƣ' => 'oi',  'Ƥ' => 'P',   'ƥ' => 'p',
        'ƫ' => 't',   'Ƭ' => 'T',   'ƭ' => 't',   'Ʈ' => 'T',   'Ư' => 'U',
        'ư' => 'u',   'Ʋ' => 'V',   'Ƴ' => 'Y',   'ƴ' => 'y',   'Ƶ' => 'Z',
        'ƶ' => 'z',   'ƻ' => '2',   'Ǆ' => 'DZ',  'ǅ' => 'Dz',  'ǆ' => 'dz',
        'Ǉ' => 'LJ',  'ǈ' => 'Lj',  'ǉ' => 'lj',  'Ǌ' => 'Nj',  'ǋ' => 'Nj',
        'ǌ' => 'nj',  'Ǎ' => 'A',   'ǎ' => 'a',   'Ǐ' => 'I',   'ǐ' => 'i',
        'Ǒ' => 'O',   'ǒ' => 'o',   'Ǔ' => 'U',   'ǔ' => 'u',   'Ǖ' => 'U',
        'ǖ' => 'u',   'Ǘ' => 'U',   'ǘ' => 'u',   'Ǚ' => 'U',   'ǚ' => 'u',
        'Ǜ' => 'U',   'ǜ' => 'u',   'ǝ' => 'e',   'Ǟ' => 'A',   'ǟ' => 'a',
        'Ǡ' => 'A',   'ǡ' => 'a',   'Ǣ' => 'AE',  'ǣ' => 'ae',  'Ǥ' => 'G',
        'ǥ' => 'g',   'Ǧ' => 'G',   'ǧ' => 'g',   'Ǩ' => 'K',   'ǩ' => 'k',
        'Ǫ' => 'O',   'ǫ' => 'o',   'Ǭ' => 'O',   'ǭ' => 'o',   'ǰ' => 'j',
        'Ǳ' => 'DZ',  'ǲ' => 'Dz',  'ǳ' => 'dz',  'Ǵ' => 'G',   'ǵ' => 'g',
        'Ǹ' => 'N',   'ǹ' => 'n',   'Ǻ' => 'A',   'ǻ' => 'a',   'Ǽ' => 'AE',
        'ǽ' => 'ae',  'Ǿ' => 'O',   'ǿ' => 'o',   'Ȁ' => 'A',   'ȁ' => 'a',
        'Ȃ' => 'A',   'ȃ' => 'a',   'Ȅ' => 'E',   'ȅ' => 'e',   'Ȇ' => 'E',
        'ȇ' => 'e',   'Ȉ' => 'I',   'ȉ' => 'i',   'Ȋ' => 'I',   'ȋ' => 'i',
        'Ȍ' => 'O',   'ȍ' => 'o',   'Ȏ' => 'O',   'ȏ' => 'o',   'Ȑ' => 'R',
        'ȑ' => 'r',   'Ȓ' => 'R',   'ȓ' => 'r',   'Ȕ' => 'U',   'ȕ' => 'u',
        'Ȗ' => 'U',   'ȗ' => 'u',   'Ș' => 'S',   'ș' => 's',   'Ț' => 'T',
        'ț' => 't',   'Ȟ' => 'H',   'ȟ' => 'h',   'Ƞ' => 'N',   'ȡ' => 'd',
        'Ȥ' => 'Z',   'ȥ' => 'z',   'Ȧ' => 'A',   'ȧ' => 'a',   'Ȩ' => 'E',
        'ȩ' => 'e',   'Ȫ' => 'O',   'ȫ' => 'o',   'Ȭ' => 'O',   'ȭ' => 'o',
        'Ȯ' => 'O',   'ȯ' => 'o',   'Ȱ' => 'O',   'ȱ' => 'o',   'Ȳ' => 'Y',
        'ȳ' => 'y',   'ȴ' => 'l',   'ȵ' => 'n',   'ȶ' => 't',   'ȷ' => 'j',
        'ȸ' => 'db',  'ȹ' => 'qp',  'Ⱥ' => 'A',   'Ȼ' => 'C',   'ȼ' => 'c',
        'Ƚ' => 'L',   'Ⱦ' => 'T',   'ȿ' => 's',   'ɀ' => 'z',   'Ƀ' => 'B',
        'Ʉ' => 'U',   'Ʌ' => 'V',   'Ɇ' => 'E',   'ɇ' => 'e',   'Ɉ' => 'J',
        'ɉ' => 'j',   'Ɋ' => 'Q',   'ɋ' => 'q',   'Ɍ' => 'R',   'ɍ' => 'r',
        'Ɏ' => 'Y',   'ɏ' => 'y',
        // IPA Extensions
        'ɐ' => 'a',   'ɓ' => 'b',   'ɔ' => 'o',   'ɕ' => 'c',   'ɖ' => 'd',
        'ɗ' => 'd',   'ɘ' => 'e',   'ɛ' => 'e',   'ɜ' => 'e',   'ɝ' => 'e',
        'ɞ' => 'e',   'ɟ' => 'j',   'ɠ' => 'g',   'ɡ' => 'g',   'ɢ' => 'G',
        'ɥ' => 'h',   'ɦ' => 'h',   'ɨ' => 'i',   'ɪ' => 'I',   'ɫ' => 'l',
        'ɬ' => 'l',   'ɭ' => 'l',   'ɯ' => 'm',   'ɰ' => 'm',   'ɱ' => 'm',
        'ɲ' => 'n',   'ɳ' => 'n',   'ɴ' => 'N',   'ɵ' => 'o',   'ɶ' => 'OE',
        'ɹ' => 'r',   'ɺ' => 'r',   'ɻ' => 'r',   'ɼ' => 'r',   'ɽ' => 'r',
        'ɾ' => 'r',   'ɿ' => 'r',   'ʀ' => 'R',   'ʁ' => 'R',   'ʂ' => 's',
        'ʇ' => 't',   'ʈ' => 't',   'ʉ' => 'u',   'ʋ' => 'v',   'ʌ' => 'v',
        'ʍ' => 'w',   'ʎ' => 'y',   'ʏ' => 'Y',   'ʐ' => 'z',   'ʑ' => 'z',
        'ʗ' => 'C',   'ʙ' => 'B',   'ʚ' => 'e',   'ʛ' => 'G',   'ʜ' => 'H',
        'ʝ' => 'j',   'ʞ' => 'k',   'ʟ' => 'L',   'ʠ' => 'q',   'ʣ' => 'dz',
        'ʥ' => 'dz',  'ʦ' => 'ts',  'ʨ' => 'tc',  'ʪ' => 'ls',  'ʫ' => 'lz',
        'ʮ' => 'h',   'ʯ' => 'h',
        // Latin Extended Additional
        'Ḁ' => 'A',   'ḁ' => 'a',   'Ḃ' => 'B',   'ḃ' => 'b',   'Ḅ' => 'B',
        'ḅ' => 'b',   'Ḇ' => 'B',   'ḇ' => 'b',   'Ḉ' => 'C',   'ḉ' => 'c',
        'Ḋ' => 'D',   'ḋ' => 'd',   'Ḍ' => 'D',   'ḍ' => 'd',   'Ḏ' => 'D',
        'ḏ' => 'd',   'Ḑ' => 'D',   'ḑ' => 'd',   'Ḓ' => 'D',   'ḓ' => 'd',
        'Ḕ' => 'E',   'ḕ' => 'e',   'Ḗ' => 'E',   'ḗ' => 'e',   'Ḙ' => 'E',
        'ḙ' => 'e',   'Ḛ' => 'E',   'ḛ' => 'e',   'Ḝ' => 'E',   'ḝ' => 'e',
        'Ḟ' => 'F',   'ḟ' => 'f',   'Ḡ' => 'G',   'ḡ' => 'g',   'Ḣ' => 'H',
        'ḣ' => 'h',   'Ḥ' => 'H',   'ḥ' => 'h',   'Ḧ' => 'H',   'ḧ' => 'h',
        'Ḩ' => 'H',   'ḩ' => 'h',   'Ḫ' => 'H',   'ḫ' => 'h',   'Ḭ' => 'I',
        'ḭ' => 'i',   'Ḯ' => 'I',   'ḯ' => 'i',   'Ḱ' => 'K',   'ḱ' => 'k',
        'Ḳ' => 'K',   'ḳ' => 'k',   'Ḵ' => 'K',   'ḵ' => 'k',   'Ḷ' => 'L',
        'ḷ' => 'l',   'Ḹ' => 'L',   'ḹ' => 'l',   'Ḻ' => 'L',   'ḻ' => 'l',
        'Ḽ' => 'L',   'ḽ' => 'l',   'Ḿ' => 'M',   'ḿ' => 'm',   'Ṁ' => 'M',
        'ṁ' => 'm',   'Ṃ' => 'M',   'ṃ' => 'm',   'Ṅ' => 'N',   'ṅ' => 'n',
        'Ṇ' => 'N',   'ṇ' => 'n',   'Ṉ' => 'N',   'ṉ' => 'n',   'Ṋ' => 'N',
        'ṋ' => 'n',   'Ṍ' => 'O',   'ṍ' => 'o',   'Ṏ' => 'O',   'ṏ' => 'o',
        'Ṑ' => 'O',   'ṑ' => 'o',   'Ṓ' => 'O',   'ṓ' => 'o',   'Ṕ' => 'P',
        'ṕ' => 'p',   'Ṗ' => 'P',   'ṗ' => 'p',   'Ṙ' => 'R',   'ṙ' => 'r',
        'Ṛ' => 'R',   'ṛ' => 'r',   'Ṝ' => 'R',   'ṝ' => 'r',   'Ṟ' => 'R',
        'ṟ' => 'r',   'Ṡ' => 'S',   'ṡ' => 's',   'Ṣ' => 'S',   'ṣ' => 's',
        'Ṥ' => 'S',   'ṥ' => 's',   'Ṧ' => 'S',   'ṧ' => 's',   'Ṩ' => 'S',
        'ṩ' => 's',   'Ṫ' => 'T',   'ṫ' => 't',   'Ṭ' => 'T',   'ṭ' => 't',
        'Ṯ' => 'T',   'ṯ' => 't',   'Ṱ' => 'T',   'ṱ' => 't',   'Ṳ' => 'U',
        'ṳ' => 'u',   'Ṵ' => 'U',   'ṵ' => 'u',   'Ṷ' => 'U',   'ṷ' => 'u',
        'Ṹ' => 'U',   'ṹ' => 'u',   'Ṻ' => 'U',   'ṻ' => 'u',   'Ṽ' => 'V',
        'ṽ' => 'v',   'Ṿ' => 'V',   'ṿ' => 'v',   'Ẁ' => 'W',   'ẁ' => 'w',
        'Ẃ' => 'W',   'ẃ' => 'w',   'Ẅ' => 'W',   'ẅ' => 'w',   'Ẇ' => 'W',
        'ẇ' => 'w',   'Ẉ' => 'W',   'ẉ' => 'w',   'Ẋ' => 'X',   'ẋ' => 'x',
        'Ẍ' => 'X',   'ẍ' => 'x',   'Ẏ' => 'Y',   'ẏ' => 'y',   'Ẑ' => 'Z',
        'ẑ' => 'z',   'Ẓ' => 'Z',   'ẓ' => 'z',   'Ẕ' => 'Z',   'ẕ' => 'z',
        'ẖ' => 'h',   'ẗ' => 't',   'ẘ' => 'w',   'ẙ' => 'y',   'ẚ' => 'a',
        'Ạ' => 'A',   'ạ' => 'a',   'Ả' => 'A',   'ả' => 'a',   'Ấ' => 'A',
        'ấ' => 'a',   'Ầ' => 'A',   'ầ' => 'a',   'Ẩ' => 'A',   'ẩ' => 'a',
        'Ẫ' => 'A',   'ẫ' => 'a',   'Ậ' => 'A',   'ậ' => 'a',   'Ắ' => 'A',
        'ắ' => 'a',   'Ằ' => 'A',   'ằ' => 'a',   'Ẳ' => 'A',   'ẳ' => 'a',
        'Ẵ' => 'A',   'ẵ' => 'a',   'Ặ' => 'A',   'ặ' => 'a',   'Ẹ' => 'E',
        'ẹ' => 'e',   'Ẻ' => 'E',   'ẻ' => 'e',   'Ẽ' => 'E',   'ẽ' => 'e',
        'Ế' => 'E',   'ế' => 'e',   'Ề' => 'E',   'ề' => 'e',   'Ể' => 'E',
        'ể' => 'e',   'Ễ' => 'E',   'ễ' => 'e',   'Ệ' => 'E',   'ệ' => 'e',
        'Ỉ' => 'I',   'ỉ' => 'i',   'Ị' => 'I',   'ị' => 'i',   'Ọ' => 'O',
        'ọ' => 'o',   'Ỏ' => 'O',   'ỏ' => 'o',   'Ố' => 'O',   'ố' => 'o',
        'Ồ' => 'O',   'ồ' => 'o',   'Ổ' => 'O',   'ổ' => 'o',   'Ỗ' => 'O',
        'ỗ' => 'o',   'Ộ' => 'O',   'ộ' => 'o',   'Ớ' => 'O',   'ớ' => 'o',
        'Ờ' => 'O',   'ờ' => 'o',   'Ở' => 'O',   'ở' => 'o',   'Ỡ' => 'O',
        'ỡ' => 'o',   'Ợ' => 'O',   'ợ' => 'o',   'Ụ' => 'U',   'ụ' => 'u',
        'Ủ' => 'U',   'ủ' => 'u',   'Ứ' => 'U',   'ứ' => 'u',   'Ừ' => 'U',
        'ừ' => 'u',   'Ử' => 'U',   'ử' => 'u',   'Ữ' => 'U',   'ữ' => 'u',
        'Ự' => 'U',   'ự' => 'u',   'Ỳ' => 'Y',   'ỳ' => 'y',   'Ỵ' => 'Y',
        'ỵ' => 'y',   'Ỷ' => 'Y',   'ỷ' => 'y',   'Ỹ' => 'Y',   'ỹ' => 'y',
        // General Punctuation
        ' ' => ' ',   ' ' => ' ',   ' ' => ' ',   ' ' => ' ',   ' ' => ' ',
        ' ' => ' ',   ' ' => ' ',   ' ' => ' ',   ' ' => ' ',   ' ' => ' ',
        ' ' => ' ',   '​' => '',    '‌' => '',    '‍' => '',    '‐' => '-',
        '‑' => '-',   '‒' => '-',   '–' => '-',   '—' => '-',   '―' => '-',
        '‖' => '||',  '‘' => "'",   '’' => "'",   '‚' => ',',   '‛' => "'",
        '“' => '"',   '”' => '"',   '‟' => '"',   '․' => '.',   '‥' => '..',
        '…' => '...', ' ' => ' ',   '′' => "'",   '″' => '"',   '‴' => '\'"',
        '‵' => "'",   '‶' => '"',   '‷' => '"\'', '‹' => '<',   '›' => '>',
        '‼' => '!!',  '‽' => '?!',  '⁄' => '/',   '⁇' => '?/',  '⁈' => '?!',
        '⁉' => '!?',
        // Letterlike Symbols
        '℠' => 'SM',  '™' => 'TM',
        // Number Forms
        '⅓' => '1/3', '⅔' => '2/3', '⅕' => '1/5', '⅖' => '2/5', '⅗' => '3/5',
        '⅘' => '4/5', '⅙' => '1/6', '⅚' => '5/6', '⅛' => '1/8', '⅜' => '3/8',
        '⅝' => '5/8', '⅞' => '7/8', 'Ⅰ' => 'I',   'Ⅱ' => 'II',  'Ⅲ' => 'III',
        'Ⅳ' => 'IV',  'Ⅴ' => 'V',   'Ⅵ' => 'Vi',  'Ⅶ' => 'VII', 'Ⅷ' => 'VIII',
        'Ⅸ' => 'IX',  'Ⅹ' => 'X',   'Ⅺ' => 'XI',  'Ⅻ' => 'XII', 'Ⅼ' => 'L',
        'Ⅽ' => 'C',   'Ⅾ' => 'D',   'Ⅿ' => 'M',   'ⅰ' => 'i',   'ⅱ' => 'ii',
        'ⅲ' => 'iii', 'ⅳ' => 'iv',  'ⅴ' => 'v',   'ⅵ' => 'vi',  'ⅶ' => 'vii',
        'ⅷ' => 'viii','ⅸ' => 'ix',  'ⅹ' => 'x',   'ⅺ' => 'xi',  'ⅻ' => 'xii',
        'ⅼ' => 'l',   'ⅽ' => 'c',   'ⅾ' => 'd',   'ⅿ' => 'm'
    );

    /**
     * All lowercase UTF-8 characters not properly handled by `mb_strtoupper()`
     *   mapped to upper-case characters.
     *
     * @var array
     */
    protected static $mbLowerToUpperFix = array(
        'ɘ' => 'Ǝ', 'ǲ' => 'Ǳ', 'ა' => 'Ⴀ', 'ბ' => 'Ⴁ', 'გ' => 'Ⴂ', 'დ' => 'Ⴃ',
        'ე' => 'Ⴄ', 'ვ' => 'Ⴅ', 'ზ' => 'Ⴆ', 'თ' => 'Ⴇ', 'ი' => 'Ⴈ', 'კ' => 'Ⴉ',
        'ლ' => 'Ⴊ', 'მ' => 'Ⴋ', 'ნ' => 'Ⴌ', 'ო' => 'Ⴍ', 'პ' => 'Ⴎ', 'ჟ' => 'Ⴏ',
        'რ' => 'Ⴐ', 'ს' => 'Ⴑ', 'ტ' => 'Ⴒ', 'უ' => 'Ⴓ', 'ფ' => 'Ⴔ', 'ქ' => 'Ⴕ',
        'ღ' => 'Ⴖ', 'ყ' => 'Ⴗ', 'შ' => 'Ⴘ', 'ჩ' => 'Ⴙ', 'ც' => 'Ⴚ', 'ძ' => 'Ⴛ',
        'წ' => 'Ⴜ', 'ჭ' => 'Ⴝ', 'ხ' => 'Ⴞ', 'ჯ' => 'Ⴟ', 'ჰ' => 'Ⴠ', 'ჱ' => 'Ⴡ',
        'ჲ' => 'Ⴢ', 'ჳ' => 'Ⴣ', 'ჴ' => 'Ⴤ', 'ჵ' => 'Ⴥ', 'ⓐ' => 'Ⓐ', 'ⓑ' => 'Ⓑ',
        'ⓒ' => 'Ⓒ', 'ⓓ' => 'Ⓓ', 'ⓔ' => 'Ⓔ', 'ⓕ' => 'Ⓕ', 'ⓖ' => 'Ⓖ', 'ⓗ' => 'Ⓗ',
        'ⓘ' => 'Ⓘ', 'ⓙ' => 'Ⓙ', 'ⓚ' => 'Ⓚ', 'ⓛ' => 'Ⓛ', 'ⓜ' => 'Ⓜ', 'ⓝ' => 'Ⓝ',
        'ⓞ' => 'Ⓞ', 'ⓟ' => 'Ⓟ', 'ⓠ' => 'Ⓠ', 'ⓡ' => 'Ⓡ', 'ⓢ' => 'Ⓢ', 'ⓣ' => 'Ⓣ',
        'ⓤ' => 'Ⓤ', 'ⓥ' => 'Ⓥ', 'ⓦ' => 'Ⓦ', 'ⓧ' => 'Ⓧ', 'ⓨ' => 'Ⓨ', 'ⓩ' => 'Ⓩ'
    );

    /**
     * All uppercase UTF-8 characters not properly handled by `mb_strtolower()`
     *   mapped to lowercase characters.
     *
     * @var array
     */
    protected static $mbUpperToLowerFix = array(
        'ǝ' => 'ɘ', 'ǅ' => 'ǆ', 'ǈ' => 'ǉ', 'ǋ' => 'ǌ', 'Ⴀ' => 'ა', 'Ⴁ' => 'ბ',
        'Ⴂ' => 'გ', 'Ⴃ' => 'დ', 'Ⴄ' => 'ე', 'Ⴅ' => 'ვ', 'Ⴆ' => 'ზ', 'Ⴇ' => 'თ',
        'Ⴈ' => 'ი', 'Ⴉ' => 'კ', 'Ⴊ' => 'ლ', 'Ⴋ' => 'მ', 'Ⴌ' => 'ნ', 'Ⴍ' => 'ო',
        'Ⴎ' => 'პ', 'Ⴏ' => 'ჟ', 'Ⴐ' => 'რ', 'Ⴑ' => 'ს', 'Ⴒ' => 'ტ', 'Ⴓ' => 'უ',
        'Ⴔ' => 'ფ', 'Ⴕ' => 'ქ', 'Ⴖ' => 'ღ', 'Ⴗ' => 'ყ', 'Ⴘ' => 'შ', 'Ⴙ' => 'ჩ',
        'Ⴚ' => 'ც', 'Ⴛ' => 'ძ', 'Ⴜ' => 'წ', 'Ⴝ' => 'ჭ', 'Ⴞ' => 'ხ', 'Ⴟ' => 'ჯ',
        'Ⴠ' => 'ჰ', 'Ⴡ' => 'ჱ', 'Ⴢ' => 'ჲ', 'Ⴣ' => 'ჳ', 'Ⴤ' => 'ჴ', 'Ⴥ' => 'ჵ',
        'ᾈ' => 'ᾀ', 'ᾉ' => 'ᾁ', 'ᾊ' => 'ᾂ', 'ᾋ' => 'ᾃ', 'ᾌ' => 'ᾄ', 'ᾍ' => 'ᾅ',
        'ᾎ' => 'ᾆ', 'ᾏ' => 'ᾇ', 'ᾘ' => 'ᾐ', 'ᾙ' => 'ᾑ', 'ᾚ' => 'ᾒ', 'ᾛ' => 'ᾓ',
        'ᾜ' => 'ᾔ', 'ᾝ' => 'ᾕ', 'ᾞ' => 'ᾖ', 'ᾟ' => 'ᾗ', 'ᾨ' => 'ᾠ', 'ᾩ' => 'ᾡ',
        'ᾪ' => 'ᾢ', 'ᾫ' => 'ᾣ', 'ᾬ' => 'ᾤ', 'ᾭ' => 'ᾥ', 'ᾮ' => 'ᾦ', 'ᾯ' => 'ᾧ',
        'Ⓐ' => 'ⓐ', 'Ⓑ' => 'ⓑ', 'Ⓒ' => 'ⓒ', 'Ⓓ' => 'ⓓ', 'Ⓔ' => 'ⓔ', 'Ⓕ' => 'ⓕ',
        'Ⓖ' => 'ⓖ', 'Ⓗ' => 'ⓗ', 'Ⓘ' => 'ⓘ', 'Ⓙ' => 'ⓙ', 'Ⓚ' => 'ⓚ', 'Ⓛ' => 'ⓛ',
        'Ⓜ' => 'ⓜ', 'Ⓝ' => 'ⓝ', 'Ⓞ' => 'ⓞ', 'Ⓟ' => 'ⓟ', 'Ⓠ' => 'ⓠ', 'Ⓡ' => 'ⓡ',
        'Ⓢ' => 'ⓢ', 'Ⓣ' => 'ⓣ', 'Ⓤ' => 'ⓤ', 'Ⓥ' => 'ⓥ', 'Ⓦ' => 'ⓦ', 'Ⓧ' => 'ⓧ',
        'Ⓨ' => 'ⓨ', 'Ⓩ' => 'ⓩ'
    );

    /**
     * {@inheritdoc}
     */
    public function isAscii($string)
    {
        return !preg_match('#[^\x00-\x7F]#', $string);
    }

    /**
     * {@inheritdoc}
     */
    public function length($string)
    {
        return mb_strlen($string, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function replace($string, $find, $replace)
    {
        return str_replace($find, $replace, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function caseInsensitiveReplace($string, $find, $replace)
    {
        if (is_array($find)) {
            foreach ($find as &$needle) {
                $needle = sprintf('#%s#ui', preg_quote($needle, '#'));
            }
        }
        else {
            $find = sprintf('#%s#ui', preg_quote($find));
        }

        $replace = strtr($replace, array('\\' => '\\\\', '$' => '\\$'));

        return preg_replace($find, $replace, $string);
    }

    /**
     * {@inheritdoc}
     */
    public function split($string, $delimiter = null)
    {
        // If a delimiter was passed, we just do an explode
        if ($delimiter || (!$delimiter && is_numeric($delimiter))) {
            return explode($delimiter, $string);
        }

        // If no delimiter was passed, we explode the characters into an array
        preg_match_all('#.|^\z#us', $string, $matches);

        return $matches[0];
    }

    /**
     * {@inheritdoc}
     */
    public function lower($string)
    {
        // We get better performance falling back for ASCII strings
        if ($this->isAscii($string)) {
            return strtolower($string);
        }

        $string = mb_strtolower($string, 'utf-8');

        // For some reason mb_strtolower misses some characters
        return strtr($string, static::$mbUpperToLowerFix);
    }

    /**
     * {@inheritdoc}
     */
    public function upper($string)
    {
        if ($this->isAscii($string)) {
            return strtoupper($string);
        }

        $string = mb_strtoupper($string, 'utf-8');

        return strtr($string, static::$mbLowerToUpperFix);
    }

    /**
     * Handles converting a character to uppercase for `#title()`.
     *
     * @param array $match The match from `#title()`.
     *
     * @return string  The uppercase character.
     *
     * @see #title()
     */
    protected function titleCallback($match)
    {
        return $this->upper($match[1]);
    }

    /**
     * {@inheritdoc}
     */
    public function title($string)
    {
        return preg_replace_callback(
            '#(?<=^|\s|[\x{2000}-\x{200A}]|/|-|\(|\[|\{|\||"|^\'|\s\'|‘|“)(.)#u',
            array('static', 'titleCallback'),
            $string
        );
    }

    /**
     * {@inheritdoc}
     */
    public function firstToUpper($string)
    {
        return $this->upper(sprintf('%s%s', $this->substr($string, 0, 1), $this->substr($string, 1)));
    }

    /**
     * {@inheritdoc}
     */
    public function trimLeft($string, $charList = null)
    {
        if (!strlen($charList)) {
            return ltrim($string);
        }

        $search = preg_quote($charList, '#');
        $search = str_replace('-', '\-', $search);
        $search = str_replace('\.\.', '-', $search);

        return preg_replace('#^[' . $search . ']+#Du', '', $string);
    }

    /**
     * {@inheritdoc}
     */
    public function trimRight($string, $charList = null)
    {
        if (!strlen($charList)) {
            return rtrim($string);
        }

        $search = preg_quote($charList, '#');
        $search = str_replace('-', '\-', $search);
        $search = str_replace('\.\.', '-', $search);

        return preg_replace('#[' . $search . ']+$#Du', '', $string);
    }

    /**
     * {@inheritdoc}
     */
    public function trim($string, $charList = null)
    {
        if (!strlen($charList)) {
            return trim($string);
        }

        $search = preg_quote($charList, '#');
        $search = str_replace('-', '\-', $search);
        $search = str_replace('\.\.', '-', $search);

        return preg_replace('#^[' . $search . ']+|[' . $search . ']+$#Du', '', $string);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($string, $needle, $offset = 0)
    {
        return mb_strpos($string, $needle, $offset, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function lastIndexOf($string, $needle, $offset = 0)
    {
        return mb_strrpos($string, $needle, $offset, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function reverse($string)
    {
        $output = '';
        $len = strlen($string);

        for ($i = 0; $i < $len; $i++) {
            $char = $string[$i];

            if (ord($char) < 128) {
                $output = $char . $output;
            }
            else {
                switch (ord($char) & 0xF0) {
                    case 0xF0:
                        $output = $string[$i] . $string[$i+1] . $string[$i+2] . $string[$i+3] . $output;
                        $i += 3;
                        break;

                    case 0xE0:
                        $output = $string[$i] . $string[$i+1] . $string[$i+2] . $output;
                        $i += 2;
                        break;

                    case 0xD0:
                    case 0xC0:
                        $output = $string[$i] . $string[$i+1] . $output;
                        $i += 1;
                        break;
                }
            }
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function wordWrap($string, $width = 75, $break = "\n", $cut = false)
    {
        if ($this->isAscii($string)) {
            return wordwrap($string, $width, $break, $cut);
        }

        $words = preg_split('#(?<=\s|[\x{2000}-\x{200A}])#ue', $string);
        $output = '';
        $lineLen = 0;

        foreach ($words as $word) {
            $wordLen = $this->length($word);

            // Shorten up words that are too long
            while ($cut && $wordLen > $width) {
                $output  .= $break;
                $output  .= $this->substr($word, 0, $width);
                $lineLen = $width;
                $word     = $this->substr($word, $width);
                $wordLen = $this->length($word);
            }

            if ($lineLen && $lineLen + $wordLen > $width) {
                $output  .= $break;
                $lineLen = 0;
            }
            $output   .= $word;
            $lineLen += $wordLen;
        }

        return $output;
    }

    /**
     * {@inheritdoc}
     */
    public function padLeft($string, $padLength, $padString = ' ')
    {
        if ($this->isAscii($string) && $this->isAscii($padString)) {
            return str_pad($string, $padLength, $padString, STR_PAD_LEFT);
        }

        $stringLength = $this->length($string);
        $padStringLength = $this->length($padString);
        $padToLength = $padLength - $stringLength;

        if ($padToLength < 1) {
            return $string;
        }

        $padded = 0;
        $side = '';

        while ($padded < $padToLength) {
            // For pad strings over 1 characters long, they may be too long to fit
            if ($padToLength - $padded < $padStringLength) {
                $padString = $this->substr($padString, 0, $padToLength - $padded);
            }

            $side .= $padString;
            $padded += $padStringLength;
        }

        return $side.$string;
    }

    /**
     * {@inheritdoc}
     */
    public function padRight($string, $padLength, $padString = ' ')
    {
        if ($this->isAscii($string) && $this->isAscii($padString)) {
            return str_pad($string, $padLength, $padString, STR_PAD_RIGHT);
        }

        $stringLength = $this->length($string);
        $padStringLength = $this->length($padString);
        $padToLength = $padLength - $stringLength;

        if ($padToLength < 1) {
            return $string;
        }

        $padded = 0;
        $side = '';

        while ($padded < $padToLength) {
            if ($padToLength - $padded < $padStringLength) {
                $padString = $this->substr($padString, 0, $padToLength - $padded);
            }

            $side .= $padString;
            $padded += $padStringLength;
        }

        return $string.$side;
    }

    /**
     * {@inheritdoc}
     */
    public function pad($string, $padLength, $padString = ' ')
    {
        if ($this->isAscii($string) && $this->isAscii($padString)) {
            return str_pad($string, $padLength, $padString, STR_PAD_BOTH);
        }

        $stringLength = $this->length($string);
        $padStringLength = $this->length($padString);
        $padToLength = $padLength - $stringLength;

        if ($padToLength < 1) {
            return $string;
        }

        $leftSide = '';
        $rightSide = '';
        $nextSide = 'left';
        $padded = 0;

        while ($padded < $padToLength) {
            if ($padToLength - $padded < $padStringLength) {
                $padString = $this->substr($padString, 0, $padToLength - $padded);
            }

            switch ($nextSide) {
                case 'right':
                    $rightSide .= $padString;
                    $nextSide = 'left';
                    break;

                case 'left':
                    $leftSide .= $padString;
                    $nextSide = 'right';
                    break;
            }

            $padded += $padStringLength;
        }

        return $leftSide.$string.$rightSide;
    }

    /**
     * {@inheritdoc}
     */
    public function clean($value)
    {
        if (!is_array($value)) {
            $oldSub = ini_get('mbstring.substitute_character');
            ini_set('mbstring.substitute_character', 'none');
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
            ini_set('mbstring.substitute_character', $oldSub);

            return $value;
        }

        $keys = array_keys($value);
        $numKeys = sizeof($keys);

        for ($i = 0; $i < $numKeys; $i++) {
            $value[$keys[$i]] = $this->clean($value[$keys[$i]]);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function substr($string, $start, $length = null)
    {
        $strLen = $this->length($string);

        if (abs($start) > $strLen) {
            return false;
        }

        if (!$length) {
            if ($start >= 0) {
                $length = $strLen - $start;
            }
            else {
                $length = abs($start);
            }
        }

        return mb_substr($string, $start, $length, 'UTF-8');
    }

    /**
     * {@inheritdoc}
     */
    public function ascii($string)
    {
        if ($this->isAscii($string)) {
            return $string;
        }

        $string = strtr($string, self::$utf8ToAscii);

        return preg_replace('#[^\x00-\x7F]#', '', $string);
    }
}
