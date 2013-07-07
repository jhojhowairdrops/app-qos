<?php

/**
 * Priority class base controller
 *
 * @category   apps
 * @package    qos
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mobile_demo/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\qos\Qos as Qos_Lib;
use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Priority class base controller
 *
 * @category   apps
 * @package    qos
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2013 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/mobile_demo/
 */

class Prioclass extends ClearOS_Controller
{
    protected $type;
    protected $pc_amount_up;
    protected $pc_amount_down;

    /**
     * Constructor
     */

    function __construct($type)
    {
        // Load dependencies
        //------------------

        $this->load->library('qos/Qos');

        // Set class variables
        //--------------------

        $this->type = $type;

        switch ($this->type) {
        case Qos_Lib::PRIORITY_CLASS_LIMIT:
            $this->pc_amount_up = "pcuplimit%d_amount";
            $this->pc_amount_down = "pcdownlimit%d_amount";
            break;
        case Qos_Lib::PRIORITY_CLASS_RESERVED:
            $this->pc_amount_up = "pcupreserved%d_amount";
            $this->pc_amount_down = "pcdownreserved%d_amount";
            break;
        default:
            // TODO: Throw exception?
        }

        // Construct parent
        //-----------------

        parent::__construct();
    }

    /**
     * Read-only index.
     */

    function index()
    {
        $this->view();
    }

    /**
     * Read-only view.
     */

    function view()
    {
        $this->_view_edit('view');
    }
    
    /**
     * Edit view.
     */

    function edit($ifn)
    {
        $this->_view_edit('edit', $ifn);
    }

    /**
     * Priority class bandwidth limit controller
     *
     * @param string $form_type form type
     *
     * @return view
     */

    function _view_edit($form_type, $ifn = NULL)
    {
        // Handle form submit
        //-------------------
        if ($this->input->post('submit-form')) {
            try {
                $values = array();
                for ($i = 0; $i < Qos_Lib::PRIORITY_CLASSES; $i++) {
                    $values['up'][$i] = $this->input->post(
                        sprintf($this->pc_amount_up, $i));
                    $values['down'][$i] = $this->input->post(
                        sprintf($this->pc_amount_down, $i));
                }
                $key = ($this->input->post('ifn') == 'all') ?
                    '*' : $this->input->post('ifn');
                $this->qos->set_priority_class_config(
                    $this->type, $key, $values['up'], $values['down']);

                redirect('/qos/qos');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load data 
        //----------
        $pc_config = $this->qos->get_priority_class_config($this->type);

        // Load views
        //-----------

        $data = array();
        $data['ifn'] = $ifn;
        $data['read_only'] = ($form_type == 'edit') ? FALSE : TRUE;
        $data['priority_classes'] = Qos_Lib::PRIORITY_CLASSES;
        $data['type_name'] =
            ($this->type == Qos_Lib::PRIORITY_CLASS_LIMIT) ? 'limit' : 'reserved';

        if ($data['read_only']) {
            $data['pc_config'] = $pc_config;
        }
        else {
            $key = ($ifn == 'all') ? '*' : $ifn;
            $data['default_values_up'] = $pc_config['up'][$key];
            $data['default_values_down'] = $pc_config['down'][$key];
        }

        $this->page->view_form('qos/prioclass', $data, lang('qos_app_name'));
    }
}

// vi: expandtab shiftwidth=4 softtabstop=4 tabstop=4
