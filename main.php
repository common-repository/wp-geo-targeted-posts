<?php  
/* 
Plugin Name: WP Geo-Targeted Post 
Plugin URI: http://wp.simep.lt/geo-targeted 
Description: Show posts only for specified countries. 
Version: 0.4 
Author: Mindaugas 
Author URI: http://www.simep.lt 
License: GPL2 
*/  

register_activation_hook(__FILE__,'gtp_activate');

/***********
 * Actions
 **********/
add_action('admin_head','gtp_admin_header');
add_action('post_submitbox_misc_actions','gtp_post');
add_action('save_post','gtp_save_countries');

/***********
 * Filters
 **********/
add_filter('pre_get_posts','hide_some_posts');

/**********************
 * Actions functions
 *********************/
function gtp_admin_header() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('select2.js', plugins_url( '/js/select2.min.js', __FILE__ ));
    wp_enqueue_script('gtp.js', plugins_url( '/js/gtp.main.js', __FILE__ ));
    wp_enqueue_style('select2.css', plugins_url( '/css/select2.css', __FILE__ ));
    wp_enqueue_style('gtp.css', plugins_url( '/css/style.css', __FILE__ ));
}

function gtp_post() {
?>
<?php 
    $gtp_meta = get_post_meta(get_the_ID(),'gtp_countries');
    $gtp_meta = $gtp_meta[0];
    $all = '<span id="gtp-country-all">All</span>';
    $gtp_meta_countries = '';
    
    if (is_array($gtp_meta)) {
        $countryList = countryList();
        foreach ($countryList as $key => $country):
            if (in_array($key,$gtp_meta)):
                $gtp_meta_countries .= '<span id="gtp-country-' . $key .'">' . $country .'</span>';
            endif;
        endforeach;
    }
    
    if (!empty($gtp_meta_countries)) { $gtp_meta_spans = $gtp_meta_countries; }
    else { $gtp_meta_spans = $all; }
?>
<div id="gtp-country" class="misc-pub-section">
    Countries for post: <a class="edit-visibility hide-if-no-js" id="gtp-edit" href="#gtp-edit" style="display: inline;">Edit</a>
    <div class="gtp-space"></div>
    <span id="gtp-country-display">
        <?php echo $gtp_meta_spans; ?>
    </span>
    <div class="hide-if-js" id="gtp-country-select" style="display: none;">
        <select class="gtp-select" multiple="multiple" name="gtp-countries-selected[]" style="width: 100%;" id="gtp-countries">
            <?php foreach (countryList() as $key => $country): ?>
            <option value="<?php echo $key; ?>" <?php echo ((is_array($gtp_meta) && in_array($key,$gtp_meta))?'selected="selected"':''); ?>><?php echo $country; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="gtp-space"></div>
        <a id="save-countries-list" class="hide-if-no-js button" href="#">Done</a>
        <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=T5J2TNM4E94LS" target="_blank"><img src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif" border="0" name="donate-button" alt="" class="gtp-donate" /></a>
    </div>
</div>
<?php
}

function gtp_save_countries($post_id) {
    if (!isset($_POST['post_type']) )
        return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) 
        return;
 
    if (!current_user_can('edit_posts',$post_id ) || !current_user_can('edit_pages',$post_id))
        return;
   
    else {
        update_post_meta($post_id,'gtp_countries', $_POST['gtp-countries-selected']);
        if (!empty($_POST['gtp-countries-selected'])) {
            update_post_meta($post_id,'gtp_countries_list',$_POST['gtp-countries-selected']);
        }
        else {
            $all = Array();
            foreach (countryList() as $key => $country) {
                array_push($all,$key);
            }
            update_post_meta($post_id,'gtp_countries_list', $all);
        }
    }
}

/**********************
 * Filters functions
 *********************/
function hide_some_posts($query) {
    if (!is_user_logged_in() && $query->is_main_query()) {
        include("geoip.inc");
        $gi = geoip_open(plugin_dir_path(__FILE__) . "GeoIP.dat",GEOIP_STANDARD);
        $country = geoip_country_code_by_addr($gi, $_SERVER['REMOTE_ADDR']);
        if ($country !== '--') {
            $query->set('meta_query',array(
                'compare' => 'OR',
                array(
                      'key' => 'gtp_countries_list',
                      'value' => '"' . $country .'"',
                      'compare' => 'LIKE'
                )
            ));
        }
    }
    return $query;
}

/**********************
 * Misc functions
 *********************/
function gtp_activate() {
    $posts = get_posts();
    $pages = get_pages();
    $all_types = array_merge($posts,$pages);
    $all = Array();
    $countryList = countryList();
    foreach ($countryList as $key => $country) {
        array_push($all,$key);
    }
    foreach ($all_types as $type) {
        $gtp_meta = get_post_meta($type->ID,'gtp_countries');
        if (empty($gtp_meta)) {
            update_post_meta($type->ID,'gtp_countries_list', $all);
        }
    }
}

function countryList(){
    return array(
'A1' => "Anonymous Proxy",
'A2' => "Satellite Provider",
'O1' => "Other Country",
'AD' => "Andorra",
'AE' => "United Arab Emirates",
'AF' => "Afghanistan",
'AG' => "Antigua and Barbuda",
'AI' => "Anguilla",
'AL' => "Albania",
'AM' => "Armenia",
'AO' => "Angola",
'AP' => "Asia/Pacific Region",
'AQ' => "Antarctica",
'AR' => "Argentina",
'AS' => "American Samoa",
'AT' => "Austria",
'AU' => "Australia",
'AW' => "Aruba",
'AX' => "Aland Islands",
'AZ' => "Azerbaijan",
'BA' => "Bosnia and Herzegovina",
'BB' => "Barbados",
'BD' => "Bangladesh",
'BE' => "Belgium",
'BF' => "Burkina Faso",
'BG' => "Bulgaria",
'BH' => "Bahrain",
'BI' => "Burundi",
'BJ' => "Benin",
'BL' => "Saint Bartelemey",
'BM' => "Bermuda",
'BN' => "Brunei Darussalam",
'BO' => "Bolivia",
'BQ' => "Bonaire, Saint Eustatius and Saba",
'BR' => "Brazil",
'BS' => "Bahamas",
'BT' => "Bhutan",
'BV' => "Bouvet Island",
'BW' => "Botswana",
'BY' => "Belarus",
'BZ' => "Belize",
'CA' => "Canada",
'CC' => "Cocos (Keeling) Islands",
'CD' => "Congo, The Democratic Republic of the",
'CF' => "Central African Republic",
'CG' => "Congo",
'CH' => "Switzerland",
'CI' => "Cote d'Ivoire",
'CK' => "Cook Islands",
'CL' => "Chile",
'CM' => "Cameroon",
'CN' => "China",
'CO' => "Colombia",
'CR' => "Costa Rica",
'CU' => "Cuba",
'CV' => "Cape Verde",
'CW' => "Curacao",
'CX' => "Christmas Island",
'CY' => "Cyprus",
'CZ' => "Czech Republic",
'DE' => "Germany",
'DJ' => "Djibouti",
'DK' => "Denmark",
'DM' => "Dominica",
'DO' => "Dominican Republic",
'DZ' => "Algeria",
'EC' => "Ecuador",
'EE' => "Estonia",
'EG' => "Egypt",
'EH' => "Western Sahara",
'ER' => "Eritrea",
'ES' => "Spain",
'ET' => "Ethiopia",
'EU' => "Europe",
'FI' => "Finland",
'FJ' => "Fiji",
'FK' => "Falkland Islands (Malvinas)",
'FM' => "Micronesia' =>  Federated States of",
'FO' => "Faroe Islands",
'FR' => "France",
'GA' => "Gabon",
'GB' => "United Kingdom",
'GD' => "Grenada",
'GE' => "Georgia",
'GF' => "French Guiana",
'GG' => "Guernsey",
'GH' => "Ghana",
'GI' => "Gibraltar",
'GL' => "Greenland",
'GM' => "Gambia",
'GN' => "Guinea",
'GP' => "Guadeloupe",
'GQ' => "Equatorial Guinea",
'GR' => "Greece",
'GS' => "South Georgia and the South Sandwich Islands",
'GT' => "Guatemala",
'GU' => "Guam",
'GW' => "Guinea-Bissau",
'GY' => "Guyana",
'HK' => "Hong Kong",
'HM' => "Heard Island and McDonald Islands",
'HN' => "Honduras",
'HR' => "Croatia",
'HT' => "Haiti",
'HU' => "Hungary",
'ID' => "Indonesia",
'IE' => "Ireland",
'IL' => "Israel",
'IM' => "Isle of Man",
'IN' => "India",
'IO' => "British Indian Ocean Territory",
'IQ' => "Iraq",
'IR' => "Iran, Islamic Republic of",
'IS' => "Iceland",
'IT' => "Italy",
'JE' => "Jersey",
'JM' => "Jamaica",
'JO' => "Jordan",
'JP' => "Japan",
'KE' => "Kenya",
'KG' => "Kyrgyzstan",
'KH' => "Cambodia",
'KI' => "Kiribati",
'KM' => "Comoros",
'KN' => "Saint Kitts and Nevis",
'KP' => "Korea, Democratic People's Republic of",
'KR' => "Korea, Republic of",
'KW' => "Kuwait",
'KY' => "Cayman Islands",
'KZ' => "Kazakhstan",
'LA' => "Lao People's Democratic Republic",
'LB' => "Lebanon",
'LC' => "Saint Lucia",
'LI' => "Liechtenstein",
'LK' => "Sri Lanka",
'LR' => "Liberia",
'LS' => "Lesotho",
'LT' => "Lithuania",
'LU' => "Luxembourg",
'LV' => "Latvia",
'LY' => "Libyan Arab Jamahiriya",
'MA' => "Morocco",
'MC' => "Monaco",
'MD' => "Moldova' =>  Republic of",
'ME' => "Montenegro",
'MF' => "Saint Martin",
'MG' => "Madagascar",
'MH' => "Marshall Islands",
'MK' => "Macedonia",
'ML' => "Mali",
'MM' => "Myanmar",
'MN' => "Mongolia",
'MO' => "Macao",
'MP' => "Northern Mariana Islands",
'MQ' => "Martinique",
'MR' => "Mauritania",
'MS' => "Montserrat",
'MT' => "Malta",
'MU' => "Mauritius",
'MV' => "Maldives",
'MW' => "Malawi",
'MX' => "Mexico",
'MY' => "Malaysia",
'MZ' => "Mozambique",
'NA' => "Namibia",
'NC' => "New Caledonia",
'NE' => "Niger",
'NF' => "Norfolk Island",
'NG' => "Nigeria",
'NI' => "Nicaragua",
'NL' => "Netherlands",
'NO' => "Norway",
'NP' => "Nepal",
'NR' => "Nauru",
'NU' => "Niue",
'NZ' => "New Zealand",
'OM' => "Oman",
'PA' => "Panama",
'PE' => "Peru",
'PF' => "French Polynesia",
'PG' => "Papua New Guinea",
'PH' => "Philippines",
'PK' => "Pakistan",
'PL' => "Poland",
'PM' => "Saint Pierre and Miquelon",
'PN' => "Pitcairn",
'PR' => "Puerto Rico",
'PS' => "Palestinian Territory",
'PT' => "Portugal",
'PW' => "Palau",
'PY' => "Paraguay",
'QA' => "Qatar",
'RE' => "Reunion",
'RO' => "Romania",
'RS' => "Serbia",
'RU' => "Russian Federation",
'RW' => "Rwanda",
'SA' => "Saudi Arabia",
'SB' => "Solomon Islands",
'SC' => "Seychelles",
'SD' => "Sudan",
'SE' => "Sweden",
'SG' => "Singapore",
'SH' => "Saint Helena",
'SI' => "Slovenia",
'SJ' => "Svalbard and Jan Mayen",
'SK' => "Slovakia",
'SL' => "Sierra Leone",
'SM' => "San Marino",
'SN' => "Senegal",
'SO' => "Somalia",
'SR' => "Suriname",
'SS' => "South Sudan",
'ST' => "Sao Tome and Principe",
'SV' => "El Salvador",
'SX' => "Sint Maarten",
'SY' => "Syrian Arab Republic",
'SZ' => "Swaziland",
'TC' => "Turks and Caicos Islands",
'TD' => "Chad",
'TF' => "French Southern Territories",
'TG' => "Togo",
'TH' => "Thailand",
'TJ' => "Tajikistan",
'TK' => "Tokelau",
'TL' => "Timor-Leste",
'TM' => "Turkmenistan",
'TN' => "Tunisia",
'TO' => "Tonga",
'TR' => "Turkey",
'TT' => "Trinidad and Tobago",
'TV' => "Tuvalu",
'TW' => "Taiwan",
'TZ' => "Tanzania' =>  United Republic of",
'UA' => "Ukraine",
'UG' => "Uganda",
'UM' => "United States Minor Outlying Islands",
'US' => "United States",
'UY' => "Uruguay",
'UZ' => "Uzbekistan",
'VA' => "Holy See (Vatican City State)",
'VC' => "Saint Vincent and the Grenadines",
'VE' => "Venezuela",
'VG' => "Virgin Islands, British",
'VI' => "Virgin Islands, U.S.",
'VN' => "Vietnam",
'VU' => "Vanuatu",
'WF' => "Wallis and Futuna",
'WS' => "Samoa",
'YE' => "Yemen",
'YT' => "Mayotte",
'ZA' => "South Africa",
'ZM' => "Zambia",
'ZW' => "Zimbabwe"
	);
}

?>