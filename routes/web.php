<?php

use App\Filament\Resources\AxisResource\Pages\EditAxesPage;
use App\Filament\Resources\SessionDepartemtnResource;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\SessionUserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\FormsController;
use App\Http\Controllers\TopicController;
use App\Models\Agenda;
use App\Filament\Resources\SessionDepartemtnResource\Pages\SessionReport;
use App\Http\Controllers\FacultySessionUserController;
use App\Http\Controllers\LDAPController;
use App\Http\Controllers\ReportCreateController;
use App\Http\Controllers\ReportsController;
use App\Models\Department;
use Illuminate\Support\Facades\File;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Livewire::setUpdateRoute(function ($handle) {
//    return Route::post('/councils/public/livewire/update', $handle);
//});

// Start faculty sessions routes
Route::post('/admin/faculty-sessions/{record}/start', [FacultySessionUserController::class, 'submitForm'])->name('attendance.submit.faculty');
Route::get('/admin/faculty-sessions/{record}/get-users-attendance', [FacultySessionUserController::class, 'getusersattandence'])->name('attendance.get-users-attendance.faculty');
Route::post('/save-faculty-session-time', [FacultySessionUserController::class, 'saveTime'])->name('saveFacultySessionTime');
Route::post('/saveFacultyAttendance', [FacultySessionUserController::class, 'saveAttendance'])->name('saveFacultyAttendance');
Route::get('/get-users-attendance/{recordId}', [FacultySessionUserController::class, 'getUsersAttendance'])->name('get.users.attendance');
Route::get('/fetch-faculty-attendance/{session_id}/{locale?}', [FacultySessionUserController::class, 'fetchAttendance'])->name('fetchFacultyAttendance');
Route::get('/fetch-faculty-voiting/{session_id}/{locale?}', [FacultySessionUserController::class, 'fetchVoiting'])->name('fetchFacultyVoiting');
Route::get('/fetch-faculty-voiting-single/{session_id}/{locale?}', [FacultySessionUserController::class, 'fetchVoitingSingle'])->name('fetchFacultyVoitingSingle');
Route::post('/saveFacultyVoiting', [FacultySessionUserController::class, 'saveVoiting'])->name('saveFacultyVoiting');
Route::post('/saveFacultyVoitingSingle', [FacultySessionUserController::class, 'saveVoitingSingle'])->name('saveVoitingSingle.faculty');
Route::post('/save-time', [FacultySessionUserController::class, 'saveTime']);
Route::get('/GetFacultyFormForSession', [FacultySessionUserController::class, 'GetFormForSession'])->name('GetFacultyFormForSession');
Route::get('/load-faculty-form-content/{locale?}', [FacultySessionUserController::class, 'loadFormContent'])->name('loadFacultyFormContent');
Route::post('/save-faculty-dession', [FacultySessionUserController::class, 'saveDecision'])->name('saveFacultyDecision');
Route::get('/view-faculty-record/{locale?}', [FacultySessionUserController::class, 'viewRecord'])->name('viewFacultyRecord');
Route::get('/faculty-session-report/{recordId}', [FacultySessionUserController::class, 'getPages'])->name('faculty-session-report');
Route::post('/admin/faculty-sessions/session-report/{recordId}', [FacultySessionUserController::class, 'decisionApproval'])->name('faculty-session-decision-approval');
Route::post('/faculty-sessions/start', [FacultySessionUserController::class, 'startStopwatch'])->name('faculty.sessions.start');
Route::get('/college-council-decision/{recordId}', [FacultySessionUserController::class, 'getPages'])->name('college-council-decision');
Route::post('/admin/college-council-decision/{recordId}', [FacultySessionUserController::class, 'saveCollegeCouncil'])->name('saveCollegeCouncil');
Route::get('/faculty-session-topics/{recordId}', [FacultySessionUserController::class, 'getPages'])->name('faculty-session-topics');
Route::get('/admin/faculty-sessions/details-report/{recordId}/{content}/pdf', [FacultySessionUserController::class, 'downloadPDF'])->name('facultyDownloadPDF');
Route::post('/faculty-session-attendance/update/signiture', [FacultySessionUserController::class, 'applySigniture'])->name('faculty-session-attendance.applySigniture');
// End faculty sessions routes

Route::post('/admin/session-departments/{record}/start', [SessionUserController::class, 'submitForm'])->name('attendance.submit');
Route::get('/admin/session-departments/{record}/get-users-attendance', [SessionUserController::class, 'getusersattandence'])->name('attendance.get-users-attendance');
Route::post('/save-time', [SessionUserController::class, 'saveTime'])->name('saveTime');
Route::post('/saveAttendance', [SessionUserController::class, 'saveAttendance'])->name('saveAttendance');
Route::get('/get-users-attendance/{recordId}', [SessionUserController::class, 'getUsersAttendance'])->name('get.users.attendance');
Route::get('/fetch-attendance/{session_id}/{locale?}', [SessionUserController::class, 'fetchAttendance'])->name('fetchAttendance');
Route::get('/fetch-voiting/{session_id}/{locale?}', [SessionUserController::class, 'fetchVoiting'])->name('fetchVoiting');
Route::get('/fetch-voiting-single/{session_id}/{locale?}', [SessionUserController::class, 'fetchVoitingSingle'])->name('fetchVoitingSingle');
Route::post('/saveVoiting', [SessionUserController::class, 'saveVoiting'])->name('saveVoiting');
Route::post('/saveVoitingSingle', [SessionUserController::class, 'saveVoitingSingle'])->name('saveVoitingSingle');

// Start Form Builder===============================================================
// Step 1
Route::get('form-builder', [FormBuilderController::class, 'index']);
// Step 2
Route::view('formbuilder', 'FormBuilder.create');
// Step 3
Route::post('save-form-builder/{locale?}', [FormBuilderController::class, 'create']);
// Step 4
Route::delete('form-delete/{id}', [FormBuilderController::class, 'destroy']);

// Step 5
Route::view('edit-form-builder/{id}', 'filament.resources.axies.pages.edit')->name('form-builder.edit');
Route::get('get-form-builder-edit', [FormBuilderController::class, 'editData'])->name('GetFormBuilderEdit');
Route::post('update-form/{locale?}', [FormBuilderController::class, 'update']);

// Step 6
Route::view('read-form-builder/{id}', 'FormBuilder.read');
Route::get('get-form-builder', [FormsController::class, 'read']);
Route::post('save-form-transaction', [FormsController::class, 'create']);

// End Form Builder===============================================================

Route::get('/admin/jquery/{id}/edit', function ($id) {
    // Your logic to handle the edit action
})->name('jquery.edit');
Route::get('/admin/jquery/{id}/edit', [FormBuilderController::class, 'edit'])->name('custom.edit.view');
// Route::get('admin/axis/{record}/edit', [EditAxesPage::class, 'render'])
//     ->name('filament.resources.axis.edit');
Route::get('/topics', [TopicController::class, 'getTopics'])->name('getTopics');
Route::get('/axies', [TopicController::class, 'getaxies'])->name('getaxies');
Route::post('/create-form-builder/{locale?}', [TopicController::class, 'createAxiesTopicForm'])->name('createAxiesTopicForm');
Route::post('/update-form-builder', [TopicController::class, 'updateForm'])->name('update-form');
Route::delete('/TopicaxisDestroy', [TopicController::class, 'destroyAxisTopic'])->name('axis.topic.delete');
Route::post('/updateTopicAxes', [TopicController::class, 'updateTopicAxessingle'])->name('updateTopicAxessingle');
Route::post('/cloneTopicAxes', [TopicController::class, 'cloneTopicAxessingle'])->name('cloneTopicAxessingle');
Route::post('/update-form-builder-topic/{locale?}', [TopicController::class, 'UpdateFormbuilderTopic'])->name('UpdateFormbuilderTopic');
Route::get('/getFaculites/{locale?}', [AgendaController::class, 'getFaculites'])->name('getFaculites');
Route::Post('/getDepartement/{locale?}', [AgendaController::class, 'getDepartement'])->name('getDepartement');
Route::get('/getTopic', [AgendaController::class, 'getTopic'])->name('getTopic');
Route::Post('/getSubTopic', [AgendaController::class, 'getSubTopic'])->name('getSubTopic');
Route::Post('/formBuilderAxsisTopic', [AgendaController::class, 'formBuilderAxsisTopic'])->name('formBuilderAxsisTopic');
Route::post('/store', [AgendaController::class, 'store'])->name('storeAgenda');
Route::post('/update', [AgendaController::class, 'update'])->name('updateAgenda');
Route::get('/GetFormForSession', [SessionUserController::class, 'GetFormForSession'])->name('GetFormForSession');
Route::post('/upload-photos', [AgendaController::class, 'uploadPhotos'])->name('uploadPhotos');
Route::post('/updateStatusAgenda', [AgendaController::class, 'updateStatusAgenda'])->name('updateStatusAgenda');

Route::put('/topic-formate/{topic_id}/{type}', [TopicController::class, 'topicFormate'])->name('topicFormate');

Route::get('/load-form-content/{locale?}', [SessionUserController::class, 'loadFormContent'])->name('loadFormContent');
Route::post('/save-dession', [SessionUserController::class, 'saveDecision'])->name('saveDecision');
Route::get('/view-record/{locale?}', [SessionUserController::class, 'viewRecord'])->name('viewRecord');
Route::get('/session-report/{recordId}', [SessionUserController::class, 'getPages'])->name('session-report');
Route::get('/add-report/{recordId}', [TopicController::class, 'getPagesReport'])->name('getPagesReportEdit');
Route::get('/add-cover-letter/{recordId}', [TopicController::class, 'getCoversReport'])->name('getCoverReportEdit');
Route::get('/session-topics/{recordId}', [SessionUserController::class, 'getPages'])->name('session-topics');
Route::get('/admin/session-departments/details-report/{recordId}/{content}/pdf', [SessionUserController::class, 'downloadPDF'])->name('downloadPDF');

Route::post('/admin/session-departemtns/session-report/{recordId}', [SessionUserController::class, 'decisionApproval'])->name('session-decision-approval');
Route::get('/agenda-topic-formbuilder', [AgendaController::class, 'AgendaTopicFormbuilder'])->name('AgendaTopicFormbuilder');
Route::post('/sessions/start', [SessionUserController::class, 'startStopwatch'])->name('sessions.start');
Route::get('/reports/create', [ReportCreateController::class, 'create'])->name('reports.create');
Route::post('/reports', [ReportCreateController::class, 'store'])->name('reports.store');
Route::post('/reports/covers-letters/{locale?}', [ReportCreateController::class, 'storeCoverLetters'])->name('reportsCoverlLetters.store');
Route::put('/reports/{id}', [ReportCreateController::class, 'update'])->name('reports.update');
Route::get('/get-topic-field-data', [ReportCreateController::class, 'getTopicFieldData'])->name('getTopicFieldData');
Route::post('/session-attendance/update/signiture', [SessionUserController::class, 'applySigniture'])->name('session-attendance.applySigniture');
Route::get('/classification-decisions', [TopicController::class, 'fetchClassificationDecisions'])->name('fetchClassificationDecisions');

Route::get('/admin/countries-json', function () {
    $path = resource_path('countries/countries.json');

    // Check if the file exists
    if (!File::exists($path)) {
        return response()->json(['error' => 'File not found'], 404);
    }

    // Get the contents of the JSON file
    $json = File::get($path);

    // Decode the JSON data to ensure it's valid
    $data = json_decode($json);

    // Return the JSON response
    return response()->json($data);
});

Route::get('/fetch-departments-agendas', [ReportsController::class, 'departmentForAgendas'])->name('reports.agenda');
Route::get('/fetch-departments-sessions', [ReportsController::class, 'departmentForSession'])->name('reports.sessions.department');
Route::get('/fetch-faculty-faculty_sessions', [ReportsController::class, 'facultyForSession'])->name('reports.sessions.faculty');

Route::post('/ldap-settings/save-configration', [LDAPController::class, 'saveSettings'])->name('ldap-settings.save');
Route::post('/ldap-settings/test-connection', [LDAPController::class, 'testConnection'])->name('ldap-settings.testConnection');
Route::post('/ldap-settings/check-ldap-user', [LDAPController::class, 'checkExistUserLdap'])->name('ldap-settings.checkExistUserLdap');
Route::post('/ldap-settings/import-users', [LDAPController::class, 'importUsers'])->name('ldap-settings.importUsers');
