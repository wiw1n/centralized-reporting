<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| REPORT FIELD REGISTRY
| -------------------------------------------------------------------------
| Single source of truth for every resident field the Report module can
| show as a column and/or filter on. Reports.php and Report_model.php only
| ever resolve field keys against this list -- client-submitted keys that
| aren't in here are silently ignored, and the SQL column/expression for a
| key never comes from the request, only from this file. That's what makes
| the report builder safe to expose to encoders.
|
| To surface a newly added `residents` column in the report builder, add
| one entry below (and nowhere else needs to change):
|
|   'column_key' => [
|       'label'      => 'Shown in the field picker & report header',
|       'column'     => 'residents.column_name'  // or a raw SQL expression
|       'type'       => 'text|number|date|boolean|enum',
|       'filterable' => true|false,
|       'options'    => [...]   // required when type = enum
|       'default'    => true,   // pre-checked in the field picker (optional)
|   ],
*/

$config['report_fields'] = [
    'identity' => [
        'label' => 'Identity',
        'fields' => [
            'resident_no' => ['label' => 'Resident No.', 'column' => 'residents.resident_no', 'type' => 'text', 'filterable' => false],
            'full_name' => ['label' => 'Full Name', 'column' => "CONCAT(residents.last_name, ', ', residents.first_name, ' ', COALESCE(residents.middle_name, ''))", 'type' => 'text', 'filterable' => false, 'default' => true],
            'last_name' => ['label' => 'Last Name', 'column' => 'residents.last_name', 'type' => 'text', 'filterable' => false],
            'first_name' => ['label' => 'First Name', 'column' => 'residents.first_name', 'type' => 'text', 'filterable' => false],
            'middle_name' => ['label' => 'Middle Name', 'column' => 'residents.middle_name', 'type' => 'text', 'filterable' => false],
            'suffix' => ['label' => 'Suffix', 'column' => 'residents.suffix', 'type' => 'text', 'filterable' => false],
            'sex' => ['label' => 'Sex', 'column' => 'residents.sex', 'type' => 'enum', 'options' => ['Male', 'Female'], 'filterable' => true, 'default' => true],
            'birthdate' => ['label' => 'Birthdate', 'column' => 'residents.birthdate', 'type' => 'date', 'filterable' => false],
            'age' => ['label' => 'Age', 'column' => 'TIMESTAMPDIFF(YEAR, residents.birthdate, CURDATE())', 'type' => 'number', 'filterable' => true, 'default' => true],
            'birthplace' => ['label' => 'Birthplace', 'column' => 'residents.birthplace', 'type' => 'text', 'filterable' => false],
            'civil_status' => ['label' => 'Civil Status', 'column' => 'residents.civil_status', 'type' => 'enum', 'options' => ['Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Live-in'], 'filterable' => true],
            'religion' => ['label' => 'Religion', 'column' => 'residents.religion', 'type' => 'text', 'filterable' => false],
            'citizenship' => ['label' => 'Citizenship', 'column' => 'residents.citizenship', 'type' => 'text', 'filterable' => false],
            'blood_type' => ['label' => 'Blood Type', 'column' => 'residents.blood_type', 'type' => 'enum', 'options' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'], 'filterable' => true],
        ],
    ],
    'location' => [
        'label' => 'Location',
        'fields' => [
            'region_name' => ['label' => 'Region', 'column' => 'address_region.name', 'type' => 'text', 'filterable' => false],
            'province_name' => ['label' => 'Province', 'column' => 'address_province.name', 'type' => 'text', 'filterable' => false],
            'municipality_name' => ['label' => 'Municipality/City', 'column' => 'address_municipality.name', 'type' => 'text', 'filterable' => false],
            'barangay_name' => ['label' => 'Barangay', 'column' => 'address_barangay.name', 'type' => 'text', 'filterable' => false, 'default' => true],
            'purok_sitio' => ['label' => 'Purok/Sitio', 'column' => 'residents.purok_sitio', 'type' => 'text', 'filterable' => false],
            'address_line' => ['label' => 'Address Line', 'column' => 'residents.address_line', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'contact' => [
        'label' => 'Contact',
        'fields' => [
            'contact_number' => ['label' => 'Contact Number', 'column' => 'residents.contact_number', 'type' => 'text', 'filterable' => false],
            'email' => ['label' => 'Email', 'column' => 'residents.email', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'work_education' => [
        'label' => 'Work & Education',
        'fields' => [
            'occupation' => ['label' => 'Occupation', 'column' => 'residents.occupation', 'type' => 'text', 'filterable' => false],
            'employer' => ['label' => 'Employer', 'column' => 'residents.employer', 'type' => 'text', 'filterable' => false],
            'monthly_income' => ['label' => 'Monthly Income', 'column' => 'residents.monthly_income', 'type' => 'number', 'filterable' => true],
            'educational_attainment' => ['label' => 'Educational Attainment', 'column' => 'residents.educational_attainment', 'type' => 'enum', 'options' => ['None', 'Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'Vocational', 'College Undergraduate', 'College Graduate', 'Post Graduate'], 'filterable' => true],
        ],
    ],
    'government_ids' => [
        'label' => 'Government IDs',
        'fields' => [
            'national_id_no' => ['label' => 'National ID (PhilSys) No.', 'column' => 'residents.national_id_no', 'type' => 'text', 'filterable' => false],
            'voters_id_no' => ['label' => "Voter's ID No.", 'column' => 'residents.voters_id_no', 'type' => 'text', 'filterable' => false],
            'sss_no' => ['label' => 'SSS No.', 'column' => 'residents.sss_no', 'type' => 'text', 'filterable' => false],
            'gsis_no' => ['label' => 'GSIS No.', 'column' => 'residents.gsis_no', 'type' => 'text', 'filterable' => false],
            'pagibig_no' => ['label' => 'Pag-IBIG No.', 'column' => 'residents.pagibig_no', 'type' => 'text', 'filterable' => false],
            'philhealth_no' => ['label' => 'PhilHealth No.', 'column' => 'residents.philhealth_no', 'type' => 'text', 'filterable' => false],
            'tin_no' => ['label' => 'TIN No.', 'column' => 'residents.tin_no', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'program_flags' => [
        'label' => 'Program Flags',
        'fields' => [
            'is_pwd' => ['label' => 'PWD', 'column' => 'residents.is_pwd', 'type' => 'boolean', 'filterable' => true],
            'is_senior_citizen' => ['label' => 'Senior Citizen', 'column' => 'residents.is_senior_citizen', 'type' => 'boolean', 'filterable' => true],
            'is_solo_parent' => ['label' => 'Solo Parent', 'column' => 'residents.is_solo_parent', 'type' => 'boolean', 'filterable' => true],
            'is_4ps_beneficiary' => ['label' => '4Ps Beneficiary', 'column' => 'residents.is_4ps_beneficiary', 'type' => 'boolean', 'filterable' => true],
            'is_ofw' => ['label' => 'OFW', 'column' => 'residents.is_ofw', 'type' => 'boolean', 'filterable' => true],
            'is_indigenous' => ['label' => 'Indigenous Person', 'column' => 'residents.is_indigenous', 'type' => 'boolean', 'filterable' => true],
            'indigenous_group' => ['label' => 'Indigenous Group', 'column' => 'residents.indigenous_group', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'other' => [
        'label' => 'Other',
        'fields' => [
            'remarks' => ['label' => 'Remarks', 'column' => 'residents.remarks', 'type' => 'text', 'filterable' => false],
            'created_at' => ['label' => 'Date Registered', 'column' => 'residents.created_at', 'type' => 'date', 'filterable' => false],
        ],
    ],
];
