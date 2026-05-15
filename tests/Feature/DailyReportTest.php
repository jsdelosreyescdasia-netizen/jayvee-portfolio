<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_daily_report_can_be_created_and_viewed(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-09',
            'title' => 'Latest Audit',
            'prepared_by' => 'Research Team',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2023-357',
                    'issuance_date' => '2023-03-22',
                    'links' => [
                        [
                            'title' => 'Presidential Decree (PD) No. 1445',
                            'remarks' => '',
                        ],
                        [
                            'title' => '2009 Revised Rules of Procedure of the COA',
                            'remarks' => 'not found',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-09 00:00:00',
            'title' => 'Latest Audit',
        ]);
        $this->assertDatabaseHas('report_issuances', [
            'issuance_no' => 'COA Decision No. 2023-357',
        ]);
        $this->assertDatabaseHas('gathered_links', [
            'title' => 'Presidential Decree (PD) No. 1445',
        ]);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('COA Decision No. 2023-357')
            ->assertSee('Presidential Decree (PD) No. 1445');
    }

    public function test_manual_entry_page_redirects_to_excel_import(): void
    {
        $this->get(route('reports.create'))
            ->assertRedirect(route('reports.import'));
    }

    public function test_daily_report_can_be_exported_as_csv(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-10',
            'title' => 'Latest Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-037',
                    'issuance_date' => '2026-02-16',
                    'links' => [
                        [
                            'title' => 'Labor Code of the Philippines',
                            'remarks' => 'not found',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('reports.export', 1));

        $response->assertOk();
        $this->assertStringContainsString('daily-report-2026-05-10.csv', $response->headers->get('content-disposition'));
    }

    public function test_daily_report_can_be_deleted_from_the_index_actions(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-14',
            'title' => 'Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-140',
                    'links' => [
                        ['title' => 'General Appropriations Act'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Delete');

        $this->delete(route('reports.destroy', 1))
            ->assertRedirect(route('reports.index'));

        $this->assertDatabaseMissing('daily_reports', [
            'report_date' => '2026-05-14 00:00:00',
        ]);
    }

    public function test_meeting_note_appears_in_daily_weekly_and_monthly_reports(): void
    {
        $meeting = 'WPD Meeting (1hr) March 30, 2026';

        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-03-30',
            'title' => 'Audit',
            'work_done' => 'Linking',
            'time_consumed' => '6.5 hrs',
            'meeting_note' => $meeting,
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2024-220',
                    'links' => [
                        ['title' => 'Audit citation'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($meeting)
            ->assertSee('6.5 hrs');

        $this->get(route('reports.weekly', ['week_start' => '2026-03-30']))
            ->assertOk()
            ->assertSee($meeting);

        $this->get(route('reports.monthly', ['month' => '2026-03']))
            ->assertOk()
            ->assertSee($meeting);
    }

    public function test_excel_paste_import_groups_rows_and_counts_gathered_entries(): void
    {
        $pastedRows = implode("\n", [
            "Issuance No.\tDate\tGathered Links\tRemarks",
            "COA Decision No. 2023-357\tMarch 22, 2023\tPresidential Decree (PD) No. 1445\t",
            "\t\t2009 Revised Rules of Procedure of the COA\t",
            "\t\tExecutive Order (EO) No. 292\t",
            "COA Decision No. 2026-037\tFebruary 16, 2026\tNFA vs. Transmonte, et al\tnot found",
            "\t\tLabor Code of the Philippines\t",
        ]);

        $response = $this->post(route('reports.import.store'), [
            'report_date' => '2026-05-11',
            'title' => 'Latest Audit',
            'work_done' => 'Linking',
            'time_consumed' => '7.5 hrs',
            'pasted_rows' => $pastedRows,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('report_issuances', [
            'issuance_no' => 'COA Decision No. 2023-357',
        ]);
        $this->assertDatabaseHas('gathered_links', [
            'title' => 'Executive Order (EO) No. 292',
        ]);
        $this->assertDatabaseHas('gathered_links', [
            'title' => 'NFA vs. Transmonte, et al',
            'remarks' => 'not found',
        ]);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('COA Decision No. 2023-357')
            ->assertSee('>3<', false)
            ->assertSee('COA Decision No. 2026-037')
            ->assertSee('>1<', false)
            ->assertSee('List of Documents')
            ->assertSee('No. of Links')
            ->assertDontSee('no. of Documents')
            ->assertSee('7.5 hrs');
    }

    public function test_excel_paste_import_supports_multiple_linking_sections_for_one_day(): void
    {
        $response = $this->post(route('reports.import.store'), [
            'report_date' => '2026-05-31',
            'title' => 'Latest Audit',
            'work_done' => 'Linking',
            'linking_sections' => [
                [
                    'title' => 'Audit',
                    'rows' => "Issuance No.\tDate\tGathered Links\tRemarks\nCOA Decision No. 2026-531\tMay 31, 2026\tAudit citation\t",
                ],
                [
                    'title' => 'SEC',
                    'rows' => "Issuance No.\tDate\tGathered Links\tRemarks\nSEC Memo No. 2026-531\tMay 31, 2026\tSEC citation\t",
                ],
                [
                    'title' => 'Insurance',
                    'rows' => "Issuance No.\tDate\tGathered Links\tRemarks\nInsurance Circular No. 2026-531\tMay 31, 2026\tInsurance citation\t",
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('report_issuances', [
            'issuance_no' => 'COA Decision No. 2026-531',
            'section_title' => 'Audit',
        ]);
        $this->assertDatabaseHas('report_issuances', [
            'issuance_no' => 'SEC Memo No. 2026-531',
            'section_title' => 'SEC',
        ]);
        $this->assertDatabaseHas('report_issuances', [
            'issuance_no' => 'Insurance Circular No. 2026-531',
            'section_title' => 'Insurance',
        ]);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('>Audit<', false)
            ->assertSee('>SEC<', false)
            ->assertSee('>Insurance<', false);

        $this->get(route('reports.weekly', ['week_start' => '2026-05-25']))
            ->assertOk()
            ->assertSee('>Audit<', false)
            ->assertSee('SEC')
            ->assertSee('Insurance');

        $this->get(route('reports.monthly', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('>Audit<', false)
            ->assertSee('SEC')
            ->assertSee('Insurance');
    }

    public function test_excel_paste_import_can_count_other_task_rows_without_linking_rows(): void
    {
        $otherTaskRows = implode("\n", [
            "Issuance number\tTitle\tdate\tremarks",
            "DENR Memorandum\tCompliance to Rules and Regulations\t01/22/2024\t",
            "DENR Administrative Order No. 2024-09\tGuidelines on Certification\t09/30/2024\t",
            "-\tDENR Citizen's Charter 2024\t2024\ttable",
        ]);

        $response = $this->post(route('reports.import.store'), [
            'report_date' => '2026-05-24',
            'title' => 'Latest Audit',
            'work_done' => 'Linking',
            'other_task_title' => 'ENR',
            'other_task_work_done' => 'Checking Documents from Premium for Fixing format',
            'time_consumed' => '7.5 hrs',
            'pasted_rows' => '',
            'other_task_rows' => $otherTaskRows,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-24 00:00:00',
            'title' => 'Latest Audit',
            'other_task' => '3',
            'other_task_title' => 'ENR',
            'other_task_work_done' => 'Checking Documents from Premium for Fixing format',
        ]);
        $this->assertDatabaseCount('report_issuances', 0);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Other Task no. of Documents')
            ->assertSee('>ENR<', false)
            ->assertSee('Checking Documents from Premium for Fixing format')
            ->assertSee('>3<', false)
            ->assertDontSee('>Linking<', false);
    }

    public function test_excel_paste_import_shows_validation_error_for_duplicate_report_date(): void
    {
        $pastedRows = "COA Decision No. 2023-357\tMarch 22, 2023\tPresidential Decree (PD) No. 1445\t";

        $this->post(route('reports.import.store'), [
            'report_date' => '2026-05-12',
            'title' => 'Latest Audit',
            'pasted_rows' => $pastedRows,
        ]);

        $response = $this->from(route('reports.import'))->post(route('reports.import.store'), [
            'report_date' => '2026-05-12',
            'title' => 'Latest Audit',
            'pasted_rows' => $pastedRows,
        ]);

        $response
            ->assertRedirect(route('reports.import'))
            ->assertSessionHasErrors(['report_date']);
    }

    public function test_different_employees_can_use_the_same_report_date(): void
    {
        $firstEmployee = auth()->user();
        $secondEmployee = User::factory()->create();
        $payload = [
            'report_date' => '2026-05-15',
            'title' => 'Latest Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-515',
                    'links' => [
                        ['title' => 'Audit citation'],
                    ],
                ],
            ],
        ];

        $this->actingAs($firstEmployee)
            ->post(route('reports.store'), $payload)
            ->assertRedirect();

        $this->actingAs($secondEmployee)
            ->post(route('reports.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('daily_reports', 2);
        $this->assertDatabaseHas('daily_reports', [
            'user_id' => $firstEmployee->id,
            'report_date' => '2026-05-15 00:00:00',
        ]);
        $this->assertDatabaseHas('daily_reports', [
            'user_id' => $secondEmployee->id,
            'report_date' => '2026-05-15 00:00:00',
        ]);
    }

    public function test_admin_can_view_all_employee_daily_reports(): void
    {
        $employee = auth()->user();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($employee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-17',
                'title' => 'Employee Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-517',
                        'links' => [
                            ['title' => 'Employee citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('View')
            ->assertDontSee('Employee Audit');

        $this->actingAs($admin)
            ->get(route('reports.index', ['employee_id' => $employee->id]))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee('May 17, 2026')
            ->assertDontSee('Employee Audit');

        $this->actingAs($admin)
            ->get(route('reports.show', 1))
            ->assertOk()
            ->assertSee('Employee citation');
    }

    public function test_admin_can_filter_weekly_and_monthly_outputs_by_employee(): void
    {
        $firstEmployee = auth()->user();
        $secondEmployee = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($firstEmployee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-19',
                'title' => 'First Employee Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-519',
                        'links' => [
                            ['title' => 'First employee citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($secondEmployee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-20',
                'title' => 'Second Employee Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-520',
                        'links' => [
                            ['title' => 'Second employee citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('reports.weekly', [
                'employee_id' => $secondEmployee->id,
                'week_start' => '2026-05-18',
            ]))
            ->assertOk()
            ->assertSee($secondEmployee->name)
            ->assertSee('Second Employee Audit')
            ->assertDontSee('First Employee Audit');

        $this->actingAs($admin)
            ->get(route('reports.monthly', [
                'employee_id' => $secondEmployee->id,
                'month' => '2026-05',
            ]))
            ->assertOk()
            ->assertSee($secondEmployee->name)
            ->assertSee('Second Employee Audit')
            ->assertDontSee('First Employee Audit');
    }

    public function test_admin_can_search_employee_for_weekly_and_monthly_outputs(): void
    {
        $firstEmployee = auth()->user();
        $secondEmployee = User::factory()->create([
            'name' => 'Rosalie Posadas',
            'email' => 'rcposadas@cdasia.com',
        ]);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($firstEmployee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-25',
                'title' => 'First Employee Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-525',
                        'links' => [
                            ['title' => 'First citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($secondEmployee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-26',
                'title' => 'Rosalie Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-526',
                        'links' => [
                            ['title' => 'Rosalie citation'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('reports.weekly', [
                'employee_search' => 'Rosalie',
                'week_start' => '2026-05-25',
            ]))
            ->assertOk()
            ->assertSee('Rosalie Posadas')
            ->assertSee('Rosalie Audit')
            ->assertDontSee('First Employee Audit');

        $this->actingAs($admin)
            ->get(route('reports.monthly', [
                'employee_search' => 'rcposadas',
                'month' => '2026-05',
            ]))
            ->assertOk()
            ->assertSee('Rosalie Posadas')
            ->assertSee('Rosalie Audit')
            ->assertDontSee('First Employee Audit');
    }

    public function test_regular_employee_search_does_not_show_other_employee_reports(): void
    {
        $employee = auth()->user();
        $otherEmployee = User::factory()->create();

        $this->actingAs($otherEmployee)
            ->post(route('reports.store'), [
                'report_date' => '2026-05-18',
                'title' => 'Private Audit',
                'issuances' => [
                    [
                        'issuance_no' => 'COA Decision No. 2026-518',
                        'links' => [
                            ['title' => 'Shared Search Term'],
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($employee)
            ->get(route('reports.index', ['search' => 'Shared Search Term']))
            ->assertOk()
            ->assertDontSee('Private Audit')
            ->assertDontSee($otherEmployee->name);
    }

    public function test_blank_work_done_defaults_to_linking(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-13',
            'title' => 'Audit',
            'work_done' => '',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-100',
                    'links' => [
                        [
                            'title' => 'Republic Act No. 9184',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-13 00:00:00',
            'work_done' => 'Linking',
        ]);
    }

    public function test_blank_time_consumed_defaults_to_seven_and_half_hours(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-16',
            'title' => 'Audit',
            'time_consumed' => '',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-160',
                    'links' => [
                        [
                            'title' => 'Budget Act',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-16 00:00:00',
            'time_consumed' => '7.5 hrs',
        ]);
    }

    public function test_daily_report_hides_other_task_column_when_blank(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-22',
            'title' => 'Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-522',
                    'links' => [
                        ['title' => 'Audit citation'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertDontSee('Other Task no. of Documents');
    }

    public function test_other_task_flows_to_daily_weekly_and_monthly_reports(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-23',
            'title' => 'ENR',
            'work_done' => 'Linking',
            'other_task' => '32',
            'other_task_title' => 'ENR',
            'other_task_work_done' => 'Checking Documents from Premium for Fixing format',
            'issuances' => [
                [
                    'issuance_no' => 'SEC Advisory',
                    'links' => [
                        ['title' => 'SEC citation'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-23 00:00:00',
            'other_task' => '32',
            'other_task_title' => 'ENR',
            'other_task_work_done' => 'Checking Documents from Premium for Fixing format',
        ]);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Other Task no. of Documents')
            ->assertSee('>Linking<', false)
            ->assertSee('>ENR<', false)
            ->assertSee('Checking Documents from Premium for Fixing format')
            ->assertSee('>32<', false);

        $this->get(route('reports.weekly', ['week_start' => '2026-05-18']))
            ->assertOk()
            ->assertSee('Other task/list of documents')
            ->assertSee('>32<', false);

        $this->get(route('reports.monthly', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('Other Tasks')
            ->assertSee('>32<', false);
    }

    public function test_whole_day_leave_can_be_recorded_without_issuances(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-27',
            'title' => 'On Leave',
            'work_done' => '',
            'leave_duration' => '1',
            'issuances' => [
                [
                    'issuance_no' => '',
                    'links' => [
                        ['title' => ''],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('daily_reports', [
            'report_date' => '2026-05-27 00:00:00',
            'leave_duration' => 1,
        ]);
        $this->assertDatabaseCount('report_issuances', 0);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Whole Day Leave');

        $this->get(route('reports.monthly', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('Whole Day Leave')
            ->assertSee('>1<', false)
            ->assertSee('>0<', false);
    }

    public function test_half_day_leave_with_linking_counts_as_half_leave_day(): void
    {
        $response = $this->post(route('reports.store'), [
            'report_date' => '2026-05-28',
            'title' => 'Audit',
            'work_done' => 'Linking',
            'leave_duration' => '0.5',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-528',
                    'links' => [
                        ['title' => 'Half-day citation'],
                    ],
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->get(route('reports.weekly', ['week_start' => '2026-05-25']))
            ->assertOk()
            ->assertSee('Audit')
            ->assertSee('Linking')
            ->assertSee('Half Day Leave')
            ->assertSee('0.5 day');

        $this->get(route('reports.monthly', ['month' => '2026-05']))
            ->assertOk()
            ->assertSee('Half Day Leave')
            ->assertSee('>0.5<', false);
    }

    public function test_leave_rows_are_not_counted_as_other_task_accomplishments(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-29',
            'title' => 'On Leave',
            'leave_duration' => '1',
            'issuances' => [
                [
                    'issuance_no' => '',
                    'links' => [
                        ['title' => ''],
                    ],
                ],
            ],
        ])->assertRedirect();

        $response = $this->get(route('reports.monthly', ['month' => '2026-05']));

        $response->assertOk()
            ->assertSee('Whole Day Leave')
            ->assertSee('<th style="text-align: left;">Other Tasks:</th>', false)
            ->assertDontSee('Whole Day Leave                        </th>', false);
    }

    public function test_weekly_report_summarizes_daily_reports(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-05',
            'title' => 'Audit',
            'work_done' => 'Linking',
            'notes' => '400',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-037',
                    'links' => [
                        ['title' => 'Labor Code of the Philippines'],
                        ['title' => 'Civil Code of the Philippines'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->post(route('reports.store'), [
            'report_date' => '2026-05-08',
            'title' => 'Audit',
            'work_done' => 'Linking',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-038',
                    'links' => [
                        ['title' => 'Procurement Law'],
                        ['title' => 'Archived item', 'remarks' => 'not found'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->post(route('reports.store'), [
            'report_date' => '2026-05-10',
            'title' => 'Weekend Audit',
            'work_done' => 'Linking',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-040',
                    'links' => [
                        ['title' => 'Weekend circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.weekly', ['week_start' => '2026-05-04']))
            ->assertOk()
            ->assertSee('May_2026')
            ->assertSee('WEEK 2')
            ->assertSee('May 05/2026')
            ->assertSee('May 10/2026')
            ->assertSee('Weekend Audit')
            ->assertSee('Audit')
            ->assertSee('>2<', false)
            ->assertSee('>4<', false)
            ->assertDontSee('400');
    }

    public function test_weekly_report_hides_weekend_rows_without_daily_reports(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-05',
            'title' => 'Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-037',
                    'links' => [
                        ['title' => 'Labor Code of the Philippines'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.weekly', ['week_start' => '2026-05-04']))
            ->assertOk()
            ->assertSee('May_2026')
            ->assertDontSee('May 09/2026')
            ->assertDontSee('May 10/2026');
    }

    public function test_weekly_report_can_be_exported_as_csv(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-05-08',
            'title' => 'Audit',
            'work_done' => '=Linking',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-039',
                    'links' => [
                        ['title' => 'Budget Circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $response = $this->get(route('reports.weekly.export', ['week_start' => '2026-05-04']));

        $response->assertOk();
        $this->assertStringContainsString('weekly-report-2026-05-04.csv', $response->headers->get('content-disposition'));
        $this->assertStringContainsString("'=Linking", $response->streamedContent());
    }

    public function test_weekly_report_defaults_to_latest_daily_report_week(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $this->post(route('reports.store'), [
            'report_date' => '2026-05-08',
            'title' => 'Current Week Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-508',
                    'links' => [
                        ['title' => 'Current circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->post(route('reports.store'), [
            'report_date' => '2026-05-11',
            'title' => 'Future Week Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-511',
                    'links' => [
                        ['title' => 'Future circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.weekly'))
            ->assertOk()
            ->assertSee('May 11, 2026 to May 17, 2026')
            ->assertSee('Future Week Audit')
            ->assertDontSee('Current Week Audit');

        Carbon::setTestNow();
    }

    public function test_monthly_report_groups_daily_reports_by_week_and_summarizes_totals(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-04-01',
            'title' => 'Latest Audit',
            'work_done' => 'Linking',
            'issuances' => [
                [
                    'issuance_no' => 'SEC Memo No. 1',
                    'links' => [
                        ['title' => 'SEC rule'],
                        ['title' => 'Old SEC rule', 'remarks' => 'not found'],
                    ],
                ],
                [
                    'issuance_no' => 'COA Decision No. 2026-001',
                    'links' => [
                        ['title' => 'Audit rule'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->post(route('reports.store'), [
            'report_date' => '2026-04-14',
            'title' => 'ENR',
            'work_done' => 'Checking Documents from Premium for Fixing format',
            'notes' => '200',
            'issuances' => [
                [
                    'issuance_no' => 'DENR Order No. 2026-001',
                    'links' => [
                        ['title' => 'Environment code'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.monthly', ['month' => '2026-04']))
            ->assertOk()
            ->assertSee('APRIL 2026')
            ->assertSee('Week 1')
            ->assertSee('Week 3')
            ->assertSee('April/01/2026')
            ->assertDontSee('Sec')
            ->assertSee('Latest Audit')
            ->assertSee('Checking Documents from Premium for Fixing format')
            ->assertSee('>2<', false)
            ->assertDontSee('>200<', false);
    }

    public function test_monthly_report_can_be_exported_as_csv(): void
    {
        $this->post(route('reports.store'), [
            'report_date' => '2026-04-30',
            'title' => '=Audit',
            'work_done' => '=Linking',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-999',
                    'links' => [
                        ['title' => 'Final circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $response = $this->get(route('reports.monthly.export', ['month' => '2026-04']));

        $response->assertOk();
        $this->assertStringContainsString('monthly-report-2026-04.csv', $response->headers->get('content-disposition'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('APRIL 2026', strtoupper($content));
        $this->assertStringContainsString("'=Linking", $content);
    }

    public function test_monthly_report_defaults_to_current_month(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $this->post(route('reports.store'), [
            'report_date' => '2026-05-01',
            'title' => 'May Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-501',
                    'links' => [
                        ['title' => 'May circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->post(route('reports.store'), [
            'report_date' => '2026-06-01',
            'title' => 'June Audit',
            'issuances' => [
                [
                    'issuance_no' => 'COA Decision No. 2026-601',
                    'links' => [
                        ['title' => 'June circular'],
                    ],
                ],
            ],
        ])->assertRedirect();

        $this->get(route('reports.monthly'))
            ->assertOk()
            ->assertSee('MAY 2026')
            ->assertSee('May Audit')
            ->assertDontSee('June Audit');

        Carbon::setTestNow();
    }
}
