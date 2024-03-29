<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\PoolTeam;
use App\Models\Registration;
use App\Models\Team;
use App\Models\TimeTable;
use App\Support\Collection;
use App\Traits\CompatitionClubsResultsTrait;
use App\Traits\HttpResponses;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationsController extends Controller
{
    use HttpResponses;
    use CompatitionClubsResultsTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Compatition $competition)
    {
        $per_page = $request->perPage;
        $competitionId = $competition->id;
        $sort = $request->sort == null ? 'compatitor_id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;

        if (Auth::user() != null) {
            if ($competition->registration_deadline <= now()) {
                $competition->update(['registration_status' => 0]);
            }
            if (Auth::user()->user_type == 0) {
                $clubId = Auth::user()->club->id;
                return RegistrationsResource::collection(Registration::orderBy('id', 'asc')->orderBy($sort, $sortDirection)->where('compatition_id', $competitionId)->where('club_id', $clubId)->paginate($per_page));
            }
            if (Auth::user()->user_type != 0 && $request->has('clubId')) {
                $clubId = $request->clubId;
                return RegistrationsResource::collection(Registration::orderBy('id', 'asc')->orderBy($sort, $sortDirection)->where('compatition_id', $competitionId)->where('club_id', $clubId)->paginate($per_page));
            }
            if (Auth::user()->user_type == 0 && Auth::user()->club == null) {
                return $this->error('', 'Molimo vas da prvo kreirate klub!', 403);
            }
            if (Auth::user()->user_type != 0) {
                if ($competition->is_abroad != 1) {
                    return RegistrationsResource::collection(Registration::orderBy('id', 'asc')->orderBy($sort, $sortDirection)->where('compatition_id', $competitionId)->paginate($per_page));
                } else {
                    return RegistrationsResource::collection(Registration::orderBy('id', 'desc')->where('compatition_id', $competitionId)->paginate($per_page));
                }
            }
        }
        if (Auth::user() == null) {
            if ($competition->registration_deadline <= now()) {
                $competition->update(['registration_status' => 0]);
            }
            return RegistrationsResource::collection(Registration::orderBy('id', 'desc')->where('compatition_id', $competitionId)->paginate($per_page));
        }
    }
    public function categoriesFiltered(Request $request, Compatition $competition)
    {

        //competition limits and data
        $applicationLimit = $competition->application_limits;
        $catTimeSpan = $competition->category_start_point;
        $competitionStartTime = new DateTime($competition->start_time_date);
        $allowedCategories = [];
        //competitior data
        if ($request->has('competitorId') && $request->competitorId != null) {
            $competitor = Compatitor::where('id', $request->competitorId)->first();
            $compatitorsBhirtDay = new DateTime($competitor->date_of_birth);
            $compatitorsYears = $compatitorsBhirtDay->diff($competitionStartTime)->y;
            if ($compatitorsYears >= 14) {
                $competitorsCategory = $catTimeSpan ? $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('years_from', '<=', $compatitorsYears)->where('years_to', '>', $compatitorsYears) : $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitor->date_of_birth)->where('date_to', '>=', $competitor->date_of_birth);
                $competitorNextCatDate =  date('Y-m-d', strtotime(' - 2 year', strtotime($competitor->date_of_birth)));
                $competitorNextCatYear = $compatitorsYears + 2;
                $nextCategories = $catTimeSpan ? $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('years_from', '<=', $competitorNextCatYear)->where('years_to', '>', $competitorNextCatYear) : $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitorNextCatDate)->where('date_to', '>=', $competitorNextCatDate);
                $maxBelt = 1;
                $alowedBelts = [];
                foreach ($competitorsCategory as $allowedCat) {
                    $allowedCategories[] = $allowedCat->id;
                    foreach ($competitorsCategory as $kateBelts) {

                        $belts = $kateBelts?->belts;
                        foreach ($belts as $belt) {
                            $alowedBelts[] = $belt->id;
                        }
                    }
                }
                if (!empty($alowedBelts)) {
                    rsort($alowedBelts);
                    $maxBelt = $alowedBelts[0] + 1;
                }
                if ($nextCategories != null) {
                    $nextCategoriesKata =  $nextCategories->where('kata_or_kumite', 1);
                    $nextCategoriesKumite = $nextCategories->where('kata_or_kumite', 0);
                }
                if ($applicationLimit == 2 && $competitor->belt_id >= $maxBelt) {
                    foreach ($nextCategoriesKata as $nextAllowedCat) {
                        if ($nextAllowedCat->belts->isEmpty()) {
                            $allowedCategories[] = $nextAllowedCat->id;
                        }
                    }
                }
                if ($applicationLimit == 2) {
                    foreach ($nextCategoriesKumite as $nextAllowedCat) {
                        $allowedCategories[] = $nextAllowedCat->id;
                    }
                }
            }
            if ($compatitorsYears < 14) {
                $competitorsCategory = $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitor->date_of_birth)->where('date_to', '>=', $competitor->date_of_birth)->sortByDesc('date_from');

                $yearCompetitor = date('Y', strtotime($competitor->date_of_birth));
                $yearCategory = date('Y', strtotime($competitorsCategory->first()?->date_from));
                $substractYears = 1 + ($yearCompetitor - $yearCategory);

                if ($compatitorsYears >= 13) {
                    $substractYears = 2;
                }
                $competitorNextCatDate =  date('Y-m-d', strtotime(" - $substractYears year", strtotime($competitor->date_of_birth)));
                $nextCategories = $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitorNextCatDate)->where('date_to', '>=', $competitorNextCatDate);
                $alowedBelts = [];
                $maxBelt = 1;

                foreach ($competitorsCategory as $allowedCat) {
                    $allowedCategories[] = $allowedCat->id;
                    foreach ($competitorsCategory as $kateBelts) {
                        $belts = $kateBelts?->belts;
                        foreach ($belts as $belt) {
                            $alowedBelts[] = $belt->id;
                        }
                    }
                }
                if (!empty($alowedBelts)) {
                    rsort($alowedBelts);
                    $maxBelt = $alowedBelts[0] + 1;
                }

                if ($nextCategories != null) {
                    $nextCategoriesKata =  $nextCategories->where('kata_or_kumite', 1);
                    $nextCategoriesKumite = $nextCategories->where('kata_or_kumite', 0);
                }

                if ($applicationLimit == 2 && $competitor->belt_id >= $maxBelt) {
                    foreach ($nextCategoriesKata as $nextAllowedCat) {
                        if ($nextAllowedCat->belts->isEmpty()) {
                            $allowedCategories[] = $nextAllowedCat->id;
                        }
                    }
                }
                if ($applicationLimit == 2) {
                    foreach ($nextCategoriesKumite as $nextAllowedCat) {
                        $allowedCategories[] = $nextAllowedCat->id;
                    }
                }
            }
        }

        return CategoriesResource::collection((new Collection($competition->categories->whereIn('id', $allowedCategories)))->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Compatition $competition)
    {
        if ($competition->registration_deadline <= now()) {
            $competition->update(['registration_status' => 0]);
        }
        if (Auth::user()->user_type != 2 && $competition->registration_status == 0) {
            return $this->error('', [['message' => 'Zatvorene su prijave!']], 403);
        }
        if (Auth::user()->user_type != 2 && Auth::user()->club->status == 0 && Auth::user()->club->country === 'Crna Gora') {
            return $this->error('', [['message' => 'Vaš nalog nije aktivan!']], 403);
        }
        //competition limits and data
        $applicationLimit = $competition->application_limits;
        $catTimeSpan = $competition->category_start_point;
        $competitionStartTime = new DateTime($competition->start_time_date);

        //competitior data
        $competitor = Compatitor::where('id', $request->competitorId)->first();
        $compatitorsBhirtDay = new DateTime($competitor->date_of_birth);
        $compatitorsYears = $compatitorsBhirtDay->diff($competitionStartTime)->y;
        $competitorStatus = Auth::user()->user_type == 0 && Auth::user()->club->country == 'Crna Gora' ? $competitor->status : 1;
        $categories = $competition->categories->whereIn('id', $request->categories);


        $registrations = $competition->registrations->where('compatitor_id', $competitor->id);
        //return $competitor->club_id;


        $arrayOfRegistrations = [];
        $responseErrorMessage = [];


        if ($request->has('competitorId') && $request->competitorId != null) {
            $competitor = Compatitor::where('id', $request->competitorId)->first();
            $compatitorsBhirtDay = new DateTime($competitor->date_of_birth);
            $compatitorsYears = $compatitorsBhirtDay->diff($competitionStartTime)->y;
            if ($compatitorsYears >= 14) {
                $competitorsCategory = $catTimeSpan ? $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('years_from', '<=', $compatitorsYears)->where('years_to', '>', $compatitorsYears) : $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitor->date_of_birth)->where('date_to', '>=', $competitor->date_of_birth);
                $competitorNextCatDate =  date('Y-m-d', strtotime(' - 2 year', strtotime($competitor->date_of_birth)));
                $competitorNextCatYear = $compatitorsYears + 2;
                $nextCategories = $catTimeSpan ? $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('years_from', '<=', $competitorNextCatYear)->where('years_to', '>', $competitorNextCatYear) : $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitorNextCatDate)->where('date_to', '>=', $competitorNextCatDate);
                $maxBelt = 1;
                $alowedBelts = [];
                foreach ($competitorsCategory as $allowedCat) {
                    $allowedCategories[] = $allowedCat->id;
                    foreach ($competitorsCategory as $kateBelts) {
                        $belts = $kateBelts?->belts;
                        foreach ($belts as $belt) {
                            $alowedBelts[] = $belt->id;
                        }
                    }
                }
                if (!empty($alowedBelts)) {
                    rsort($alowedBelts);
                    $maxBelt = $alowedBelts[0] + 1;
                }
                if ($nextCategories != null) {
                    $nextCategoriesKata =  $nextCategories->where('kata_or_kumite', 1);
                    $nextCategoriesKumite = $nextCategories->where('kata_or_kumite', 0);
                }
                if ($applicationLimit == 2 && $competitor->belt_id >= $maxBelt) {
                    foreach ($nextCategoriesKata as $nextAllowedCat) {
                        if ($nextAllowedCat->belts->isEmpty()) {
                            $allowedCategories[] = $nextAllowedCat->id;
                        }
                    }
                }
                if ($applicationLimit == 2) {
                    foreach ($nextCategoriesKumite as $nextAllowedCat) {
                        $allowedCategories[] = $nextAllowedCat->id;
                    }
                }
            }
            if ($compatitorsYears < 14) {
                $competitorsCategory = $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitor->date_of_birth)->where('date_to', '>=', $competitor->date_of_birth)->sortByDesc('date_from');

                $yearCompetitor = date('Y', strtotime($competitor->date_of_birth));
                $yearCategory = date('Y', strtotime($competitorsCategory->first()?->date_from));
                $substractYears = 1 + ($yearCompetitor - $yearCategory);

                if ($compatitorsYears >= 13) {
                    $substractYears = 2;
                }
                $competitorNextCatDate =  date('Y-m-d', strtotime(" - $substractYears year", strtotime($competitor->date_of_birth)));
                $nextCategories = $competition->categories->whereIn('gender', [$competitor->gender, 3])->where('solo_or_team', 1)->where('date_from', '<=', $competitorNextCatDate)->where('date_to', '>=', $competitorNextCatDate);
                $alowedBelts = [];
                $maxBelt = 1;

                foreach ($competitorsCategory as $allowedCat) {
                    $allowedCategories[] = $allowedCat->id;
                    foreach ($competitorsCategory as $kateBelts) {
                        $belts = $kateBelts?->belts;
                        foreach ($belts as $belt) {
                            $alowedBelts[] = $belt->id;
                        }
                    }
                }
                if (!empty($alowedBelts)) {
                    rsort($alowedBelts);
                    $maxBelt = $alowedBelts[0] + 1;
                }

                if ($nextCategories != null) {
                    $nextCategoriesKata =  $nextCategories->where('kata_or_kumite', 1);
                    $nextCategoriesKumite = $nextCategories->where('kata_or_kumite', 0);
                }

                if ($applicationLimit == 2 && $competitor->belt_id >= $maxBelt) {
                    foreach ($nextCategoriesKata as $nextAllowedCat) {
                        if ($nextAllowedCat->belts->isEmpty()) {
                            $allowedCategories[] = $nextAllowedCat->id;
                        }
                    }
                }
                if ($applicationLimit == 2) {
                    foreach ($nextCategoriesKumite as $nextAllowedCat) {
                        $allowedCategories[] = $nextAllowedCat->id;
                    }
                }
            }
        }



        $kataCount = 0 + $registrations->where('team_or_single', 1)->where('kata_or_kumite', 1)->count();
        $kumiteCount = 0 + $registrations->where('team_or_single', 1)->where('kata_or_kumite', 0)->count();
        $dateKumiteFrom = date(now());
        foreach ($categories as $category) {
            $isItSingle = $category->solo_or_team;
            $isItKata = $category->kata_or_kumite;
            $gender = $category->gender != 3 ? $category->gender : $competitor->gender;
            $dateFrom = $catTimeSpan && $category->years_to != null ? date('Y-m-d', strtotime($competition->start_time_date . " -$category->years_to years")) : $category->date_from;
            $dateTo = $catTimeSpan && $category->years_from != null ? date('Y-m-d', strtotime($competition->start_time_date . " -$category->years_from years - 1 day")) : $category->date_to;
            $belts = $category->belts;
            $genderLetter = $gender == 1 ? 'M' : 'Ž';
            $categoryName = $category->name;
            $categoryLevel = $category->category_name;
            $noErrors = true;


            if ($competitor->club->country == 'Montenegro' && $competitor->club->status == 0 || $competitorStatus == 0) {
                $error['message'] = "Takmičaru $competitor->name $competitor->last_name nema validan status!";
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }

            if ($applicationLimit == 2 && !in_array($category->id, $allowedCategories)) {
                $error['message'] = "Takmičaru $competitor->name $competitor->last_name ova kategorija nije dozvoljena!";
                $error['category'] = (string)$category->id;
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }

            if (!$isItSingle) {
                $error['message'] = "Ekipne kategorije ne mogu biti prijavljene ovom metodom prijava!";
                $error['category'] = (string)$category->id;
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }
            if ($applicationLimit == 1 && ($dateFrom > $competitor->date_of_birth || $competitor->date_of_birth > $dateTo)) {
                $error['message'] = "Takmičar $competitor->name $competitor->last_name se ne može prijaviti u kategoriji: $genderLetter $categoryName $categoryLevel!";
                $error['category'] = (string)$category->id;
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }
            if (!$belts->isEmpty()) {
                $beltChecker = true;
                foreach ($belts as $belt) {
                    if ($belt->id == $competitor->belt->id) {
                        $beltChecker = false;
                    }
                }
                if ($beltChecker) {
                    $error['message'] = "Takmičar $competitor->name $competitor->last_name ne posjeduje adekvatan pojas za kategoriju: $genderLetter $category->name $category->category_name!";
                    $error['category'] = (string)$category->id;
                    $responseErrorMessage[] = $error;
                    $noErrors = false;
                    continue;
                }
            }
            if ($competitor->gender != $gender) {
                $error['message'] = "Takmičar $competitor->name $competitor->last_name ne može biti prijavljen u $genderLetter kategoriju!";
                $error['category'] = (string)$category->id;
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }
            if ($registrations->where('category_id', $category->id)->count() != 0) {
                $error['message'] = "Takmičar $competitor->name $competitor->last_name je već prijavljen u $genderLetter $category->name $category->category_name!";
                $error['category'] = (string)$category->id;
                $responseErrorMessage[] = $error;
                $noErrors = false;
                continue;
            }
            if ($isItKata) {
                $kateRealCount = $kataCount;
                $kataCount = $kataCount + 1;
                $katText = $kateRealCount == 1 ? 'kategoriji' : 'kategorije';
                if ($kataCount > $applicationLimit) {
                    $error['message'] = "Takmičar $competitor->name $competitor->last_name ne može biti prijavljen u više od $kateRealCount $katText Kate!";
                    $error['category'] = (string)$category->id;
                    $responseErrorMessage[] = $error;
                    $noErrors = false;
                    continue;
                }
            }

            if (!$isItKata) {
                $kumiteRealCount = $kumiteCount;
                $kumiteCount = $kumiteCount + 1;
                $katText = $kumiteRealCount == 1 ? 'kategoriji' : 'kategorije';

                $registeredKumite = $registrations->where('kata_or_kumite', 0)->where('team_or_single', 1);

                if (!!$dateKumiteFrom && $dateKumiteFrom == $category->date_from && $category->category_name != 'OPEN') {

                    $error['message'] = "Takmičar $competitor->name $competitor->last_name je već prijavljen u jednoj težinskoj kategoriji u ovom godištu!";
                    $error['category'] = (string)$category->id;
                    $responseErrorMessage[] = $error;
                    $noErrors = false;
                    continue;
                }


                if ($registeredKumite->count() > 0) {
                    $registeredkumiteCat = Category::where('id', $registeredKumite->first()->category_id)->first();

                    if ($registeredkumiteCat->category_name != 'OPEN' && $category->category_name != 'OPEN' && $registeredkumiteCat->date_from == $category->date_from) {
                        $error['message'] = "Takmičar $competitor->name $competitor->last_name je već prijavljen u jednoj težinskoj kategoriji u ovom godištu!";
                        $error['category'] = (string)$category->id;
                        $responseErrorMessage[] = $error;
                        $noErrors = false;
                        continue;
                    }
                }
                if ($kumiteCount > $applicationLimit) {
                    $error['message'] = "Takmičar $competitor->name $competitor->last_name ne može biti prijavljen u više od $applicationLimit. $katText Kumite!";
                    $error['category'] = (string)$category->id;
                    $responseErrorMessage[] = $error;
                    $noErrors = false;
                    continue;
                }
                $dateKumiteFrom = $category->date_from;
            }
            if ($noErrors) {
                $input['compatition_id'] = $competition->id;
                $input['club_id'] = $competitor->club_id != null ? $competitor->club->id : null;
                $input['compatitor_id'] = $competitor->id;
                $input['category_id'] = $category->id;
                $input['team_id'] = null;
                $input['team_or_single'] = $category->solo_or_team;
                $input['kata_or_kumite'] = $category->kata_or_kumite;
                $input['created_at'] = date("Y:m:d H:i:s");
                $input['updated_at'] = date("Y:m:d H:i:s");
                $input['status'] = 1;
                $arrayOfRegistrations[] = $input;
            }
        }

        //updates data for registrated clubs

        if (count($responseErrorMessage) == 0) {
            Registration::insert($arrayOfRegistrations);
            $this->calculateResults($competition->id, [$competitor->club_id], 'registrations');
            return $this->success('', 'Registracija uspješna!');
        }
        return $this->error('', $responseErrorMessage, 403);
    }
    public function newStore(Request $request, Compatition $competition)
    {
        $applicationLimit = $competition->application_limits;
        $competitionType = $competition->type;
        $category = $competition->categories->where('id', $request->categoryId)->first();
        $isItSingle = $category->solo_or_team;
        $isItKata = $category->kata_or_kumite;
        $isItMale = $category->gender == 1;
        $isItFemale = $category->gender == 2;
        $dateTo = $category->date_to;
        $competitorsIds = $request->competitors;
        $competitiors = Compatitor::whereIn('id', $competitorsIds)->get();
        $registrations = $competition->registrations->where('team_or_single', $isItSingle)->where('kata_or_kumite', $isItKata);
        $arrayOfRegistrations = [];
        $arrayOfClubs = [];
        $responseErrorMessage = [];


        if (Auth::user()->user_type != 2 && $competition->registration_status == 0) {
            $team['message'] = 'Prijave su trenutno onemogućene ili su istekle!';
            $responseErrorMessage[] =  $team;
        }

        if (!$isItSingle && $isItKata && ($isItMale || $isItFemale) && ($competitiors->count() < 3 || $competitiors->count() > 4)) {
            $team['message'] =  "Nema dovoljno takmičara u ekipi, minimum 3 a maksimum 4 takmičara!";
            $responseErrorMessage[] =  $team;
        }
        if ($competitionType == 'Turniri' && !$isItSingle && !$isItKata && ($isItMale || $isItFemale) && ($competitiors->count() < 3 || $competitiors->count() > 4)) {
            $team['message'] =  "Nema dovoljno takmičara u ekipi minimum 5 a maksimum 7 takmičara!";
            $responseErrorMessage[] =  $team;
        }
        if ($competitionType == 'KSCG' && !$isItSingle && !$isItKata && $isItFemale && ($competitiors->count() < 3 || $competitiors->count() > 4)) {
            $team['message'] =  "Nema dovoljno takmičara u ekipi minimum 5 a maksimum 7 takmičara!";
            $responseErrorMessage[] =  $team;
        }
        if ($competitionType == 'KSCG' && !$isItSingle && !$isItKata && $isItMale && ($competitiors->count() < 5 || $competitiors->count() > 7)) {
            $team['message'] =  "Nema dovoljno takmičara u ekipi minimum 5 a maksimum 7 takmičara!";
            $responseErrorMessage[] =  $team;
        }
        if (!$isItSingle) {
            $teamName = "Ekipa ";
            $teamNumber = $competition->teams()->count() + 1;

            $team = $competition->teams()->create([
                'name' => $teamName . $teamNumber
            ]);
        }
        foreach ($competitiors as $competitor) {
            $isItError = false;
            $categoryError = false;
            $olderCategoryError = false;
            $genderError = false;
            $beltError = false;
            $generationError = false;
            $arrayOfClubs[] = $competitor->club_id;
            $competitorStatus = Auth::user()->user_type == 0 && Auth::user()->club->country == 'Crna Gora' ? $competitor->status : 1;




            if ($isItSingle && $registrations->where('compatitor_id', $competitor->id)->where('kata_or_kumite', $isItKata)->count() >= $applicationLimit) {
                $isItError = true;
            }
            if ($registrations->where('compatitor_id', $competitor->id)->where('category_id', $category->id)->count() >= 1) {
                $categoryError = true;
            }
            if ($isItSingle && $isItKata && $competitor->date_of_birth > $dateTo && $competitor->belt->id < 7) {
                $olderCategoryError = true;
            }
            if ($category->gender != 3 && $category->gender != $competitor->gender) {
                $genderError = true;
            }

            if ($isItSingle && $isItKata && !$category->belts->isEmpty()) {
                $corrector = false;
                foreach ($category->belts as $belt) {
                    if ($belt->id == $competitor->belt_id) {
                        $corrector = true;
                    }
                }
                if ($corrector == false) {
                    $beltError = true;
                }
            }
            if ($isItSingle && ($isItKata || !$isItKata) && $competitor->date_of_birth > $dateTo && $applicationLimit == 1) {
                $generationError = true;
            }

            if ($competitor->club->country == 'Montenegro' &&  $competitor->club->status == 0 || $competitorStatus == 0) {
                $error['message'] = "Takmičaru $competitor->name $competitor->last_name nema validan status!";
                $responseErrorMessage[] = $error;
            }
            if (!$isItError && !$categoryError && !$olderCategoryError && !$genderError && !$beltError && !$generationError) {
                $input['compatition_id'] = $competition->id;
                $input['club_id'] = $competitor->club_id != null ? $competitor->club->id : null;
                $input['compatitor_id'] = $competitor->id;
                $input['category_id'] = $category->id;
                $input['team_id'] = $isItSingle ? null : $team->id;
                $input['team_or_single'] = $category->solo_or_team;
                $input['kata_or_kumite'] = $category->kata_or_kumite;
                $input['created_at'] = date("Y:m:d H:i:s");
                $input['updated_at'] = date("Y:m:d H:i:s");
                $input['status'] = 1;
                $arrayOfRegistrations[] = $input;
            }
            if ($isItError) {
                $limitedCount = $isItSingle ? '2 prijave' : '1 prijavu';
                $singleOrTeam = $isItSingle ? 'pojedinčnom' : 'timskom';
                $kateOrKumite = $isItKata ? 'kate' : 'kumite';
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName ima $limitedCount u $singleOrTeam nastupu $kateOrKumite!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($categoryError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName je već prijavljen u ovoj kategoriji!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($olderCategoryError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName nije u apsolutnom nivou pa se ne moze prijaviti u starijem godištu!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($genderError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Pol takmičara $name $lastName nije adekvatan za ovu kategoriju!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($beltError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičara $name $lastName nema adekvatan pojas za ovu kategoriju!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($generationError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičara $name $lastName moze da se prijavi samo u svom godištu!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
        }

        if (count($responseErrorMessage) == 0) {
            Registration::insert($arrayOfRegistrations);
            $this->calculateResults($competition->id, array_unique($arrayOfClubs));
            return $this->success('', 'Registracija uspješna!');
        }
        return $this->error($responseErrorMessage, 'Provjerite podatke!', 403);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Registration $registration)
    {
        if ($request->has('status')) {
            $registration->update(['is_printed' => $request->status]);
            return $this->success('', 'Uspješno imjenjen status štampanja.');
        }
        if ($request->has('position') && $registration->compatition->is_abroad) {
            $positionConvert = $request->position == 1 ? 3 : ($request->position == 3 ? 1 : $request->position);
            $registration->update(['position' => $positionConvert]);
            $this->calculateResults($registration->compatition_id, [$registration->club_id], 'registrations');
            $this->calculateResults($registration->compatition_id, [$registration->club_id], 'results');
            return $this->success('', 'Uspješno dodata pozicija.');
        }
        return $this->error('', 'Only status can be chaged', 403);
    }
    public function updateMany(Request $request)
    {
        // public function store(Request $request)
        // {
        //     $data = [];
        //     foreach($request->all() as $kata) {
        //         $input['name'] = $kata['name'];
        //         $input['created_at'] = now();
        //         $input['updated_at'] = now();
        //         $data[] = $input;
        //     }
        //     OfficialKata::insert($data );
        //     return $this->success('', 'Done!');
        // }
        $registrationNew = null;
        foreach ($request->all() as $data) {
            $registration = Registration::where('id', $data['registrationId'])->first();
            if ($registration != null) {
                $registrationNew = $registration;
                $allRegistrations = Registration::where('compatition_id', $registration->compatition_id)->where('category_id', $registration->category_id)->get();
                foreach ($allRegistrations as $reg) {
                    $reg->update(['position' => NULL]);
                }
                $this->calculateResults($registration->compatition_id, [$registration->club_id], 'registrations');
                $this->calculateResults($registration->compatition_id, [$registration->club_id], 'results');
            }
        }
        foreach ($request->all() as $data) {


            $registration = Registration::where('id', $data['registrationId'])->first();
            if ($registration != null) {
                $positionConvert = $data['position'] == 1 ? 3 : ($data['position'] == 3 ? 1 : $data['position']);
                $registration->update(['position' => $positionConvert, 'status' => 1]);
                $this->calculateResults($registration->compatition_id, [$registration->club_id], 'registrations');
                $this->calculateResults($registration->compatition_id, [$registration->club_id], 'results');
                $registrationNew = $registration;
            }
        }
        // return $this->success('', $registrationNew);
        TimeTable::where('compatition_id', $registrationNew->compatition_id)->where('category_id', $registrationNew->category_id)->update(['status' => 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
        return $this->success('', 'Uspješno dodata pozicije.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Registration $registration)
    {

        if (Auth::user()->user_type != 2 && $registration->compatition->registration_status == 0) {
            return $this->error('', 'Prijave su trenutno onemogućene ili su istekle!', 403);
        }
        $category = Category::where('id', $registration->category_id)->first();
        if ($registration->team_or_single == 0) {
            $teamId = $registration->team_id;
            $categoryGender = $category->gender;
            $teamDelete = Registration::where('team_id', $teamId)->get();
            $team = Team::where('id', $teamId)->first();
            $teamOne = PoolTeam::where('team_one', $teamId)->get();
            $teamTwo = PoolTeam::where('team_two', $teamId)->get();
            $toUpdate = 0;

            //should be 5
            if ($registration->compatition->type == 'KSCG' && $category->kata_or_kumite == 0 && $categoryGender == 1 && $teamDelete->count() - 1 < 5) {
                $toUpdate = 1;
                foreach ($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                $team->delete();
            }
            if ($registration->compatition->type == 'Turniri' && $category->kata_or_kumite == 0 && $categoryGender == 1 && $teamDelete->count() - 1 < 3) {
                $toUpdate = 1;
                foreach ($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                $team->delete();
            }
            if ($category->kata_or_kumite == 1 && $categoryGender == 1 && $teamDelete->count() - 1 < 3) {
                $toUpdate = 1;
                foreach ($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                $team->delete();
            }
            if ($category->kata_or_kumite == 0 && $categoryGender == 2 && $teamDelete->count() - 1 < 3) {
                $toUpdate = 1;
                foreach ($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                $team->delete();
            }
            if ($category->kata_or_kumite == 1 && $categoryGender == 2 && $teamDelete->count() - 1 < 3) {
                $toUpdate = 1;
                foreach ($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                $team->delete();
            }
            if ($toUpdate == 1 && $teamOne->count() > 0) {
                $teamOne->first()->update(['team_one' => null]);
            }
            if ($toUpdate == 1 && $teamTwo->count() > 0) {
                $teamTwo->first()->update(['team_two' => null]);
            }
            if ($toUpdate == 1) {
                return $this->success('', 'Uspješno obrisana ekipa!');
            }
        }
        $clubId = $registration->club_id;
        $registration->delete();
        $this->calculateResults($registration->compatition_id, [$clubId], 'registrations');
        return $this->success('', 'Uspješno obrisana registracija!');
    }
    public function calculateResultsNow(Compatition $compatition)
    {
        $this->calculateResults($compatition->id, [], 'registrations');
        $this->calculateResults($compatition->id, [], 'results');
    }
}
