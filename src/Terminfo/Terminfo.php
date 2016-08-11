<?php

/**
 * This file is part of the ZerusTech package.
 *
 * (c) Michael Lee <michael.lee@zerustech.com>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace ZerusTech\Component\Terminal\Terminfo;

/**
 * The Terminfo class parses compiled terminfo data.
 *
 * The terminfo is parsed into structured data with capability names, which are
 * defined in ncurses file: ``include/Caps``.
 *
 * @author Michael Lee <michael.lee@zerustech.com>
 */
class Terminfo
{
    /**
     * Key of 'name' in terminfo array.
     */
    const KEY_NAME = 'name';

    /**
     * Key of 'description' in terminfo array.
     */
    const KEY_DESCRIPTION = 'description';

    /**
     * Key of 'booleans section' in terminfo array.
     */
    const KEY_BOOLEANS = Parser::KEY_BOOLEANS;

    /**
     * Key of 'numbers section' in terminfo array.
     */
    const KEY_NUMBERS = Parser::KEY_NUMBERS;

    /**
     * Key of 'strings section' in terminfo array.
     */
    const KEY_STRINGS = Parser::KEY_STRINGS;

    /**
     * @var Parser The parser passes raw terminfo into structured data.
     */
    private $parser = null;

    /**
     * @var string The terminal name of current terminfo instance.
     */
    private $term = null;

    /**
     * @var string The prefix of all terminfo system directories.
     */
    private $root = '';

    /**
     * The parsed terminfo.
     *
     * The associative array that contains capability names and values:
     *
     *     [
     *         'name' => ...,
     *         'description' => ...,
     *         'booleans' => [...],
     *         'numbers' => [...],
     *         'strings' => [...]
     *     ]
     *
     * @var array The terminfo array.
     */
    private $terminfo = null;

    /**
     * @var array The boolean capability names.
     */
    private static $booleanNames = [
        'auto_left_margin',
        'auto_right_margin',
        'no_esc_ctlc',
        'ceol_standout_glitch',
        'eat_newline_glitch',
        'erase_overstrike',
        'generic_type',
        'hard_copy',
        'has_meta_key',
        'has_status_line',
        'insert_null_glitch',
        'memory_above',
        'memory_below',
        'move_insert_mode',
        'move_standout_mode',
        'over_strike',
        'status_line_esc_ok',
        'dest_tabs_magic_smso',
        'tilde_glitch',
        'transparent_underline',
        'xon_xoff',
        'needs_xon_xoff',
        'prtr_silent',
        'hard_cursor',
        'non_rev_rmcup',
        'no_pad_char',
        'non_dest_scroll_region',
        'can_change',
        'back_color_erase',
        'hue_lightness_saturation',
        'col_addr_glitch',
        'cr_cancels_micro_mode',
        'has_print_wheel',
        'row_addr_glitch',
        'semi_auto_right_margin',
        'cpi_changes_res',
        'lpi_changes_res',
        'backspaces_with_bs',
        'crt_no_scrolling',
        'no_correctly_working_cr',
        'gnu_has_meta_key',
        'linefeed_is_newline',
        'has_hardware_tabs',
        'return_does_clr_eol',
    ];

    /**
     * @var array The number capability names.
     */
    private static $numberNames = [
        'columns',
        'init_tabs',
        'lines',
        'lines_of_memory',
        'magic_cookie_glitch',
        'padding_baud_rate',
        'virtual_terminal',
        'width_status_line',
        'num_labels',
        'label_height',
        'label_width',
        'max_attributes',
        'maximum_windows',
        'max_colors',
        'max_pairs',
        'no_color_video',
        'buffer_capacity',
        'dot_vert_spacing',
        'dot_horz_spacing',
        'max_micro_address',
        'max_micro_jump',
        'micro_col_size',
        'micro_line_size',
        'number_of_pins',
        'output_res_char',
        'output_res_line',
        'output_res_horz_inch',
        'output_res_vert_inch',
        'print_rate',
        'wide_char_size',
        'buttons',
        'bit_image_entwining',
        'bit_image_type',
        'magic_cookie_glitch_ul',
        'carriage_return_delay',
        'new_line_delay',
        'backspace_delay',
        'horizontal_tab_delay',
        'number_of_function_keys',
    ];

    /**
     * @var array The string capability names.
     */
    private static $stringNames = [
        'back_tab',
        'bell',
        'carriage_return',
        'change_scroll_region',
        'clear_all_tabs',
        'clear_screen',
        'clr_eol',
        'clr_eos',
        'column_address',
        'command_character',
        'cursor_address',
        'cursor_down',
        'cursor_home',
        'cursor_invisible',
        'cursor_left',
        'cursor_mem_address',
        'cursor_normal',
        'cursor_right',
        'cursor_to_ll',
        'cursor_up',
        'cursor_visible',
        'delete_character',
        'delete_line',
        'dis_status_line',
        'down_half_line',
        'enter_alt_charset_mode',
        'enter_blink_mode',
        'enter_bold_mode',
        'enter_ca_mode',
        'enter_delete_mode',
        'enter_dim_mode',
        'enter_insert_mode',
        'enter_secure_mode',
        'enter_protected_mode',
        'enter_reverse_mode',
        'enter_standout_mode',
        'enter_underline_mode',
        'erase_chars',
        'exit_alt_charset_mode',
        'exit_attribute_mode',
        'exit_ca_mode',
        'exit_delete_mode',
        'exit_insert_mode',
        'exit_standout_mode',
        'exit_underline_mode',
        'flash_screen',
        'form_feed',
        'from_status_line',
        'init_1string',
        'init_2string',
        'init_3string',
        'init_file',
        'insert_character',
        'insert_line',
        'insert_padding',
        'key_backspace',
        'key_catab',
        'key_clear',
        'key_ctab',
        'key_dc',
        'key_dl',
        'key_down',
        'key_eic',
        'key_eol',
        'key_eos',
        'key_f0',
        'key_f1',
        'key_f10',
        'key_f2',
        'key_f3',
        'key_f4',
        'key_f5',
        'key_f6',
        'key_f7',
        'key_f8',
        'key_f9',
        'key_home',
        'key_ic',
        'key_il',
        'key_left',
        'key_ll',
        'key_npage',
        'key_ppage',
        'key_right',
        'key_sf',
        'key_sr',
        'key_stab',
        'key_up',
        'keypad_local',
        'keypad_xmit',
        'lab_f0',
        'lab_f1',
        'lab_f10',
        'lab_f2',
        'lab_f3',
        'lab_f4',
        'lab_f5',
        'lab_f6',
        'lab_f7',
        'lab_f8',
        'lab_f9',
        'meta_off',
        'meta_on',
        'newline',
        'pad_char',
        'parm_dch',
        'parm_delete_line',
        'parm_down_cursor',
        'parm_ich',
        'parm_index',
        'parm_insert_line',
        'parm_left_cursor',
        'parm_right_cursor',
        'parm_rindex',
        'parm_up_cursor',
        'pkey_key',
        'pkey_local',
        'pkey_xmit',
        'print_screen',
        'prtr_off',
        'prtr_on',
        'repeat_char',
        'reset_1string',
        'reset_2string',
        'reset_3string',
        'reset_file',
        'restore_cursor',
        'row_address',
        'save_cursor',
        'scroll_forward',
        'scroll_reverse',
        'set_attributes',
        'set_tab',
        'set_window',
        'tab',
        'to_status_line',
        'underline_char',
        'up_half_line',
        'init_prog',
        'key_a1',
        'key_a3',
        'key_b2',
        'key_c1',
        'key_c3',
        'prtr_non',
        'char_padding',
        'acs_chars',
        'plab_norm',
        'key_btab',
        'enter_xon_mode',
        'exit_xon_mode',
        'enter_am_mode',
        'exit_am_mode',
        'xon_character',
        'xoff_character',
        'ena_acs',
        'label_on',
        'label_off',
        'key_beg',
        'key_cancel',
        'key_close',
        'key_command',
        'key_copy',
        'key_create',
        'key_end',
        'key_enter',
        'key_exit',
        'key_find',
        'key_help',
        'key_mark',
        'key_message',
        'key_move',
        'key_next',
        'key_open',
        'key_options',
        'key_previous',
        'key_print',
        'key_redo',
        'key_reference',
        'key_refresh',
        'key_replace',
        'key_restart',
        'key_resume',
        'key_save',
        'key_suspend',
        'key_undo',
        'key_sbeg',
        'key_scancel',
        'key_scommand',
        'key_scopy',
        'key_screate',
        'key_sdc',
        'key_sdl',
        'key_select',
        'key_send',
        'key_seol',
        'key_sexit',
        'key_sfind',
        'key_shelp',
        'key_shome',
        'key_sic',
        'key_sleft',
        'key_smessage',
        'key_smove',
        'key_snext',
        'key_soptions',
        'key_sprevious',
        'key_sprint',
        'key_sredo',
        'key_sreplace',
        'key_sright',
        'key_srsume',
        'key_ssave',
        'key_ssuspend',
        'key_sundo',
        'req_for_input',
        'key_f11',
        'key_f12',
        'key_f13',
        'key_f14',
        'key_f15',
        'key_f16',
        'key_f17',
        'key_f18',
        'key_f19',
        'key_f20',
        'key_f21',
        'key_f22',
        'key_f23',
        'key_f24',
        'key_f25',
        'key_f26',
        'key_f27',
        'key_f28',
        'key_f29',
        'key_f30',
        'key_f31',
        'key_f32',
        'key_f33',
        'key_f34',
        'key_f35',
        'key_f36',
        'key_f37',
        'key_f38',
        'key_f39',
        'key_f40',
        'key_f41',
        'key_f42',
        'key_f43',
        'key_f44',
        'key_f45',
        'key_f46',
        'key_f47',
        'key_f48',
        'key_f49',
        'key_f50',
        'key_f51',
        'key_f52',
        'key_f53',
        'key_f54',
        'key_f55',
        'key_f56',
        'key_f57',
        'key_f58',
        'key_f59',
        'key_f60',
        'key_f61',
        'key_f62',
        'key_f63',
        'clr_bol',
        'clear_margins',
        'set_left_margin',
        'set_right_margin',
        'label_format',
        'set_clock',
        'display_clock',
        'remove_clock',
        'create_window',
        'goto_window',
        'hangup',
        'dial_phone',
        'quick_dial',
        'tone',
        'pulse',
        'flash_hook',
        'fixed_pause',
        'wait_tone',
        'user0',
        'user1',
        'user2',
        'user3',
        'user4',
        'user5',
        'user6',
        'user7',
        'user8',
        'user9',
        'orig_pair',
        'orig_colors',
        'initialize_color',
        'initialize_pair',
        'set_color_pair',
        'set_foreground',
        'set_background',
        'change_char_pitch',
        'change_line_pitch',
        'change_res_horz',
        'change_res_vert',
        'define_char',
        'enter_doublewide_mode',
        'enter_draft_quality',
        'enter_italics_mode',
        'enter_leftward_mode',
        'enter_micro_mode',
        'enter_near_letter_quality',
        'enter_normal_quality',
        'enter_shadow_mode',
        'enter_subscript_mode',
        'enter_superscript_mode',
        'enter_upward_mode',
        'exit_doublewide_mode',
        'exit_italics_mode',
        'exit_leftward_mode',
        'exit_micro_mode',
        'exit_shadow_mode',
        'exit_subscript_mode',
        'exit_superscript_mode',
        'exit_upward_mode',
        'micro_column_address',
        'micro_down',
        'micro_left',
        'micro_right',
        'micro_row_address',
        'micro_up',
        'order_of_pins',
        'parm_down_micro',
        'parm_left_micro',
        'parm_right_micro',
        'parm_up_micro',
        'select_char_set',
        'set_bottom_margin',
        'set_bottom_margin_parm',
        'set_left_margin_parm',
        'set_right_margin_parm',
        'set_top_margin',
        'set_top_margin_parm',
        'start_bit_image',
        'start_char_set_def',
        'stop_bit_image',
        'stop_char_set_def',
        'subscript_characters',
        'superscript_characters',
        'these_cause_cr',
        'zero_motion',
        'char_set_names',
        'key_mouse',
        'mouse_info',
        'req_mouse_pos',
        'get_mouse',
        'set_a_foreground',
        'set_a_background',
        'pkey_plab',
        'device_type',
        'code_set_init',
        'set0_des_seq',
        'set1_des_seq',
        'set2_des_seq',
        'set3_des_seq',
        'set_lr_margin',
        'set_tb_margin',
        'bit_image_repeat',
        'bit_image_newline',
        'bit_image_carriage_return',
        'color_names',
        'define_bit_image_region',
        'end_bit_image_region',
        'set_color_band',
        'set_page_length',
        'display_pc_char',
        'enter_pc_charset_mode',
        'exit_pc_charset_mode',
        'enter_scancode_mode',
        'exit_scancode_mode',
        'pc_term_options',
        'scancode_escape',
        'alt_scancode_esc',
        'enter_horizontal_hl_mode',
        'enter_left_hl_mode',
        'enter_low_hl_mode',
        'enter_right_hl_mode',
        'enter_top_hl_mode',
        'enter_vertical_hl_mode',
        'set_a_attributes',
        'set_pglen_inch',
        'termcap_init2',
        'termcap_reset',
        'linefeed_if_not_lf',
        'backspace_if_not_bs',
        'other_non_function_keys',
        'arrow_key_map',
        'acs_ulcorner',
        'acs_llcorner',
        'acs_urcorner',
        'acs_lrcorner',
        'acs_ltee',
        'acs_rtee',
        'acs_btee',
        'acs_ttee',
        'acs_hline',
        'acs_vline',
        'acs_plus',
        'memory_lock',
        'memory_unlock',
        'box_chars_1',
    ];

    /**
     * Constructor.
     *
     * The ``$term`` parameter is the terminal name, defaults to null. When
     * ``$term`` is null, the terminal name will be detected automatically
     *
     * The ``$root`` parameter is the prefix of all system directories where
     * terminfo file could be found.
     *
     * By default, the prefix is ``''``, so the system directories start from
     * ``/``, ``/usr/share/terminfo``, for example.
     *
     * This parameter is useful when system directories are under a directory,
     * ``/opt/local``, for example. In which case, the prefix is ``/opt/local``.
     *
     * @param string $term The terminal name, defaults to null.
     * @param string $root The prefix of the terminfo system directories, defaults to ''.
     */
    public function __construct($term = null, $root = '')
    {
        $this->term = $term;
        $this->root = $root;
        $path = static::getTerminfoPath($term, $root);
        $data = file_get_contents($path);
        $this->parser = new Parser($data);
    }

    /**
     * Gets the terminfo parser.
     *
     * @return Parser The internal terminfo parser.
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Gets the terminal name of current terminfo instance.
     *
     * @return string The terminal name.
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * Gets the prefix of all terminfo system directories.
     *
     * @return string The prefix.
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Gets the parsed terminfo that contains capability names and values.
     *
     * @return array The parsed terminfo.
     */
    public function getTerminfo()
    {
        if (!$this->parser->isParsed()) {

            $this->parser->parse();
        }

        if (null === $this->terminfo) {

            $parsed = $this->parser->getTerminfo();

            $terminfo = [];

            $names = $parsed[Parser::KEY_NAMES];

            list($terminfo[self::KEY_NAME], $terminfo[self::KEY_DESCRIPTION]) = explode('|', $names);

            $booleans = $parsed[Parser::KEY_BOOLEANS];

            $terminfo[self::KEY_BOOLEANS] = [];

            $booleanNames = &static::$booleanNames;

            for ($i = 0; $i < count($booleans); $i++) {

                $terminfo[self::KEY_BOOLEANS][$booleanNames[$i]] = $booleans[$i];
            }

            $terminfo[self::KEY_NUMBERS] = [];

            $numbers = $parsed[Parser::KEY_NUMBERS];

            $numberNames = &static::$numberNames;

            for ($i = 0; $i < count($numbers); $i++) {

                $terminfo[self::KEY_NUMBERS][$numberNames[$i]] = $numbers[$i];
            }

            $terminfo[self::KEY_STRINGS] = [];

            $strings = $parsed[Parser::KEY_STRINGS];

            $stringNames = &static::$stringNames;

            for ($i = 0; $i < count($strings); $i++) {

                $terminfo[self::KEY_STRINGS][$stringNames[$i]] = $strings[$i];
            }

            $this->terminfo = $terminfo;
        }

        return $this->terminfo;
    }

    /**
     * Gets the default terminal name.
     *
     * It tries to detect default terminal name from ``$_SERVER['TERM']``.
     * If it does not exist, ``xterm`` is used.
     *
     * @return string The default terminal name.
     */
    public static function getDefaultTerm()
    {
        // $win = defined('PHP_WINDOWS_VERSION_PLATFORM');
        // return isset($_SERVER['TERM']) && !empty($_SERVER['TERM']) ? $_SERVER['TERM'] : ($win ? 'windows-ansi' : 'xterm');
        return isset($_SERVER['TERM']) && !empty($_SERVER['TERM']) ? $_SERVER['TERM'] : 'xterm';
    }

    /**
     * Gets path to the first terminfo file that matches that given terminal name.
     *
     * If terminal name is null, the default terminal name will be used (refer
     * to {@link \ZerusTech\Component\Terminal\Terminfo\Terminfo::getDefaultTerm()}
     * for details), otherwise, if no terminfo file could be found for the given
     * terminal name, it will try to find a terminal file for 'xterm'.
     *
     * Looks for terminfo file in the following directories:
     * - ``$_SERVER['TERMINFO']``
     * - ``$_SERVER['HOME']/.terminfo``
     * - Each directory in ``$_SERVER['TERMINFO_DIRS']``
     * - System directories:
     *   - ``PREFIX/usr/share/terminfo``
     *   - ``PREFIX/usr/share/lib/terminfo``
     *   - ``PREFIX/lib/terminfo``
     *   - ``PREFIX/usr/lib/terminfo``
     *   - ``PREFIX/usr/local/share/terminfo``
     *   - ``PREFIX/usr/local/share/lib/terminfo``
     *   - ``PREFIX/usr/local/lib/terminfo``
     *   - ``PREFIX/usr/local/ncurses/lib/terminfo``
     *   - ``PREFIX/opt/local/share/terminfo``
     *   - ``PREFIX/opt/local/lib/terminfo``
     *
     * @param string $term The terminal name.
     * @param string $root The prefix of root directory, defaults to ''.
     * @return string Path to the terminfo file.
     * @see getRoot()
     * @see getDefaultTerm()
     */
    public static function getTerminfoPath($term = null, $root = '')
    {
        $paths = [];

        if (isset($_SERVER['TERMINFO'])) {

            $paths[] = $root.$_SERVER['TERMINFO'];
        }

        if (isset($_SERVER['HOME'])) {

            $paths[] = $root.$_SERVER['HOME'].'/.terminfo';
        }

        if (isset($_SERVER['TERMINFO_DIRS'])) {

            $terminfoDirs = explode(':', $_SERVER['TERMINFO_DIRS']);

            foreach ($terminfoDirs as $path) {

                $paths[] = $root.$path;
            }
        }

        $paths[] = $root.'/usr/share/terminfo';
        $paths[] = $root.'/usr/share/lib/terminfo';
        $paths[] = $root.'/lib/terminfo';
        $paths[] = $root.'/usr/lib/terminfo';
        $paths[] = $root.'/usr/local/share/terminfo';
        $paths[] = $root.'/usr/local/share/lib/terminfo';
        $paths[] = $root.'/usr/local/lib/terminfo';
        $paths[] = $root.'/usr/local/ncurses/lib/terminfo';
        $paths[] = $root.'/opt/local/share/terminfo';
        $paths[] = $root.'/opt/local/lib/terminfo';

        $term = $term ? : static::getDefaultTerm();
        $fileHexa = dechex(ord($term[0])).'/'.$term;
        $fileAlpha = $term[0].'/'.$term;

        $pathName = null;

        foreach ($paths as $path) {

            if (file_exists($currentPath = $path.'/'.$fileHexa) || file_exists($currentPath = $path.'/'.$fileAlpha)) {

                $pathName = $currentPath;

                break;
            }
        }

        if (null === $pathName && 'xterm' !== $term) {

            $pathName = static::getTerminfoPath('xterm', $root);
        }

        return $pathName;
    }

    /**
     * Returns value of the given boolean capability.
     *
     * @param string $name The capability name.
     * @return bool The capability value.
     */
    public function getBoolean($name)
    {
        $terminfo = $this->getTerminfo();

        return isset($terminfo[self::KEY_BOOLEANS][$name]) ? $terminfo[self::KEY_BOOLEANS][$name] : null;
    }

    /**
     * Returns value of the given number capability.
     *
     * @param string $name The capability name.
     * @return int The capability value.
     */
    public function getNumber($name)
    {
        $terminfo = $this->getTerminfo();

        return isset($terminfo[self::KEY_NUMBERS][$name]) ? $terminfo[self::KEY_NUMBERS][$name] : null;
    }

    /**
     * Returns value of the given string capability.
     *
     * @param string $name The capability name.
     * @return string The capability value.
     */
    public function getString($name)
    {
        $terminfo = $this->getTerminfo();

        return isset($terminfo[self::KEY_STRINGS][$name]) ? $terminfo[self::KEY_STRINGS][$name] : null;
    }

    /**
     * Gets all boolean capability names.
     *
     * @return string[] The boolean capability names.
     */
    public static function getBooleanNames()
    {
        return static::$booleanNames;
    }

    /**
     * Gets all numeric capability names.
     *
     * @return string[] The numeric capability names.
     */
    public static function getNumberNames()
    {
        return static::$numberNames;
    }

    /**
     * Gets all string capability names.
     *
     * @return string[] The string capability names.
     */
    public static function getStringNames()
    {
        return static::$stringNames;
    }
}
