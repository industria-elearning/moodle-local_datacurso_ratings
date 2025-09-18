<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_datacurso_ratings
 * @category    admin
 * @copyright   Josue <josue@datacurso.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Crear categoría propia para el plugin en la administración.
    $ADMIN->add('localplugins', new admin_category(
        'local_datacurso_ratings_category',
        get_string('pluginname', 'local_datacurso_ratings')
    ));

    // Añadir un enlace hacia feedback.php.
    $ADMIN->add('local_datacurso_ratings_category', new admin_externalpage(
        'local_datacurso_ratings_feedback',
        get_string('managefeedback', 'local_datacurso_ratings'),
        new moodle_url('/local/datacurso_ratings/admin/feedback.php'),
        'moodle/site:config' // Capacidad requerida.
    ));

    // Enlace para ver el reporte administrativo de calificaciones.
    $ADMIN->add('local_datacurso_ratings_category', new admin_externalpage(
        'local_datacurso_ratings_report',
        get_string('ratingsreport', 'local_datacurso_ratings'),
        new moodle_url('/local/datacurso_ratings/admin/report.php'),
        'moodle/site:config'
    ));

    // New page of general settings.
    $settingspage = new admin_settingpage(
        'local_datacurso_ratings_settings',
        get_string('generalsettings', 'local_datacurso_ratings')
    );

    // Checkbox: enabled plugin default in all courses.
    $settingspage->add(new admin_setting_configcheckbox(
        'local_datacurso_ratings/enabled',
        get_string('enableplugin', 'local_datacurso_ratings'),
        get_string('enableplugin_desc', 'local_datacurso_ratings'),
        1
    ));

    $ADMIN->add('local_datacurso_ratings_category', $settingspage);
}
