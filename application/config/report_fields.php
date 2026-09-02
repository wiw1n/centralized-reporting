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
| To surface a newly added resident column in the report builder, add one
| entry below. `column` may reference `residents` or any of the per-section
| child tables that Report_model::base_query() left-joins (resident_personal,
| resident_contact, resident_work_education, resident_government_ids,
| resident_program_flags, resident_remarks). A brand-new table also needs its
| LEFT JOIN adding there; the existing ones are already joined.
|
|   'column_key' => [
|       'label'      => 'Shown in the field picker & report header',
|       'column'     => 'resident_personal.column_name'  // or a raw SQL expression
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
            'resident_no' => ['label' => 'Resident ID Number', 'column' => 'residents.resident_no', 'type' => 'text', 'filterable' => false],
            'full_name' => ['label' => 'Full Name', 'column' => "CONCAT(residents.last_name, ', ', residents.first_name, ' ', COALESCE(residents.middle_name, ''))", 'type' => 'text', 'filterable' => false, 'default' => true],
            'last_name' => ['label' => 'Last Name', 'column' => 'residents.last_name', 'type' => 'text', 'filterable' => false],
            'first_name' => ['label' => 'First Name', 'column' => 'residents.first_name', 'type' => 'text', 'filterable' => false],
            'middle_name' => ['label' => 'Middle Name', 'column' => 'residents.middle_name', 'type' => 'text', 'filterable' => false],
            'suffix' => ['label' => 'Suffix', 'column' => 'residents.suffix', 'type' => 'text', 'filterable' => false],
            'sex' => ['label' => 'Sex', 'column' => 'residents.sex', 'type' => 'enum', 'options' => ['Male', 'Female'], 'filterable' => true, 'default' => true],
            'birthdate' => ['label' => 'Birthdate', 'column' => 'residents.birthdate', 'type' => 'date', 'filterable' => false],
            'age' => ['label' => 'Age', 'column' => 'TIMESTAMPDIFF(YEAR, residents.birthdate, CURDATE())', 'type' => 'number', 'filterable' => true, 'default' => true],
            'birthplace' => ['label' => 'Birthplace', 'column' => 'resident_personal.birthplace', 'type' => 'text', 'filterable' => false],
            'civil_status' => ['label' => 'Civil Status', 'column' => 'resident_personal.civil_status', 'type' => 'enum', 'options' => ['Single', 'Married', 'Widowed', 'Separated', 'Divorced', 'Live-in'], 'filterable' => true],
            'religion' => ['label' => 'Religion', 'column' => 'resident_personal.religion', 'type' => 'text', 'filterable' => false],
            'citizenship' => ['label' => 'Citizenship', 'column' => 'resident_personal.citizenship', 'type' => 'text', 'filterable' => false],
            'blood_type' => ['label' => 'Blood Type', 'column' => 'resident_personal.blood_type', 'type' => 'enum', 'options' => ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'], 'filterable' => true],
        ],
    ],
    'location' => [
        'label' => 'Location',
        'fields' => [
            'region_name' => ['label' => 'Region', 'column' => 'address_region.name', 'type' => 'text', 'filterable' => false],
            'province_name' => ['label' => 'Province', 'column' => 'address_province.name', 'type' => 'text', 'filterable' => false],
            'municipality_name' => ['label' => 'Municipality/City', 'column' => 'address_municipality.name', 'type' => 'text', 'filterable' => false],
            'barangay_name' => ['label' => 'Barangay', 'column' => 'address_barangay.name', 'type' => 'text', 'filterable' => false, 'default' => true],
            'purok_sitio' => ['label' => 'Purok/Sitio', 'column' => 'resident_contact.purok_sitio', 'type' => 'text', 'filterable' => false],
            'address_line' => ['label' => 'Address Line', 'column' => 'resident_contact.address_line', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'contact' => [
        'label' => 'Contact',
        'fields' => [
            'contact_number' => ['label' => 'Contact Number', 'column' => 'resident_contact.contact_number', 'type' => 'text', 'filterable' => false],
            'email' => ['label' => 'Email', 'column' => 'resident_contact.email', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'work_education' => [
        'label' => 'Work & Education',
        'fields' => [
            'occupation' => ['label' => 'Occupation', 'column' => 'resident_work_education.occupation', 'type' => 'text', 'filterable' => false],
            'employer' => ['label' => 'Employer', 'column' => 'resident_work_education.employer', 'type' => 'text', 'filterable' => false],
            'monthly_income' => ['label' => 'Monthly Income', 'column' => 'resident_work_education.monthly_income', 'type' => 'number', 'filterable' => true],
            'educational_attainment' => ['label' => 'Educational Attainment', 'column' => 'resident_work_education.educational_attainment', 'type' => 'enum', 'options' => ['None', 'Elementary Undergraduate', 'Elementary Graduate', 'High School Undergraduate', 'High School Graduate', 'Vocational', 'College Undergraduate', 'College Graduate', 'Post Graduate'], 'filterable' => true],
        ],
    ],
    'government_ids' => [
        'label' => 'Government IDs',
        'fields' => [
            'national_id_no' => ['label' => 'National ID (PhilSys) No.', 'column' => 'resident_government_ids.national_id_no', 'type' => 'text', 'filterable' => false],
            'voters_id_no' => ['label' => "Voter's ID No.", 'column' => 'resident_government_ids.voters_id_no', 'type' => 'text', 'filterable' => false],
            'sss_no' => ['label' => 'SSS No.', 'column' => 'resident_government_ids.sss_no', 'type' => 'text', 'filterable' => false],
            'gsis_no' => ['label' => 'GSIS No.', 'column' => 'resident_government_ids.gsis_no', 'type' => 'text', 'filterable' => false],
            'pagibig_no' => ['label' => 'Pag-IBIG No.', 'column' => 'resident_government_ids.pagibig_no', 'type' => 'text', 'filterable' => false],
            'philhealth_no' => ['label' => 'PhilHealth No.', 'column' => 'resident_government_ids.philhealth_no', 'type' => 'text', 'filterable' => false],
            'tin_no' => ['label' => 'TIN No.', 'column' => 'resident_government_ids.tin_no', 'type' => 'text', 'filterable' => false],
            'yakap_no' => ['label' => 'Yakap No.', 'column' => 'resident_government_ids.yakap_no', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'program_flags' => [
        'label' => 'Program Flags',
        'fields' => [
            'is_pwd' => ['label' => 'PWD', 'column' => 'resident_program_flags.is_pwd', 'type' => 'boolean', 'filterable' => true],
            'is_senior_citizen' => ['label' => 'Senior Citizen', 'column' => 'resident_program_flags.is_senior_citizen', 'type' => 'boolean', 'filterable' => true],
            'is_solo_parent' => ['label' => 'Solo Parent', 'column' => 'resident_program_flags.is_solo_parent', 'type' => 'boolean', 'filterable' => true],
            'is_4ps_beneficiary' => ['label' => '4Ps Beneficiary', 'column' => 'resident_program_flags.is_4ps_beneficiary', 'type' => 'boolean', 'filterable' => true],
            'is_ofw' => ['label' => 'OFW', 'column' => 'resident_program_flags.is_ofw', 'type' => 'boolean', 'filterable' => true],
            'is_indigenous' => ['label' => 'Indigenous Person', 'column' => 'resident_program_flags.is_indigenous', 'type' => 'boolean', 'filterable' => true],
            'indigenous_group' => ['label' => 'Indigenous Group', 'column' => 'resident_program_flags.indigenous_group', 'type' => 'text', 'filterable' => false],
        ],
    ],
    'other' => [
        'label' => 'Other',
        'fields' => [
            'remarks' => ['label' => 'Remarks', 'column' => 'resident_remarks.remarks', 'type' => 'text', 'filterable' => false],
            'created_at' => ['label' => 'Date Registered', 'column' => 'residents.created_at', 'type' => 'date', 'filterable' => false],
        ],
    ],
];
