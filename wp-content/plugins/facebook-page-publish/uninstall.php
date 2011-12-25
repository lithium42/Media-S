<?php
/**
 * Facebook Page Publish - publishes your blog posts to your fan page.
 * Copyright (C) 2011  Martin Tschirsich
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Uninstall hook called automatically by WP (recognized by its
 * filename).
 */
 
if (!defined('WP_UNINSTALL_PLUGIN')) exit();
 
delete_option('fpp_installed_version');
delete_option('fpp_options');
delete_option('fpp_object_access_token');
delete_option('fpp_profile_access_token');
delete_option('fpp_error');
//delete_option('fpp_warning');
?>