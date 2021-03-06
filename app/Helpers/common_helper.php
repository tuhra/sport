<?php
use App\Model\Media;
use App\Model\ArticleCategory;
use App\Model\Favourite;
use Propaganistas\LaravelPhone\PhoneNumber;

function saveSingleMedia($request, $upload_type)
{
    $media_paths = config('global')['MEDIA_PATH'];
    $media_types = config('global')['MEDIA_TYPE'];
    $mediaField = $media_types[$upload_type];
    $upload_path = $media_paths[config('global')[$request->media_path]] . "/" . date("Y") . "/" . date("m") . "/";
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0775, true);
        file_put_contents($upload_path . "/index.html", "");
    }

    $file = $request->file($mediaField['field_name']);
    $time = substr(number_format(time() * mt_rand(), 0, '', ''), 0, 10);
    $data = array(
        'file_name' => $time . "." . $file->getClientOriginalExtension(),
        'file_path' => $upload_path,
        'file_type' => $file->getClientOriginalExtension(),
        'file_size' => $file->getClientSize(),
        'file_caption' => $file->getClientOriginalName()
    );
    $result = saveUploadMedia($file, $data, $mediaField);
    if ($result['status'] == TRUE) {
        $result['media_id'] = Media::insertGetId($data);
    }
    return $result;
}

function saveMultipleMedia(Request $request, $upload_type)
{
    // $media_paths = json_decode(MEDIA_PATH, true);
    // $media_types = json_decode(MEDIA_TYPE, true);
    $media_paths = config('global')['MEDIA_PATH'];
    $media_types = config('global')['MEDIA_TYPE'];
    $mediaField = $media_types[$upload_type];
    return $mediaField;

    $upload_path = $media_paths[$request->media_path] . "/" . date("Y") . "/" . date("m") . "/";
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0775, true);
        file_put_contents($upload_path . "/index.html", "");
    }

    $media_ids = array('media_id' => array(), 'status' => FALSE);
    foreach ($request->file($mediaField['field_name']) as $file) {
        $time = substr(number_format(time() * mt_rand(), 0, '', ''), 0, 10);
        $data = array(
            'file_name' => $time . "." . $file->getClientOriginalExtension(),
            'file_path' => $upload_path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getClientSize(),
            'file_caption' => $file->getClientOriginalName()
        );
        $result = saveUploadMedia($file, $data, $mediaField);
        if ($result['status'] == TRUE) {
            $media_ids['media_id'][] = Media::insertGetId($data);
        } else {
            return array('status' => FALSE, 'message' => $result['message']);
        }
    }
    $media_ids['status'] = TRUE;
    return $media_ids;
}

function saveUploadMedia($file, $data, $mediaField)
{
    $target_file = $data['file_path'] . $data['file_name'];
    $uploadOk = 1;

    if (file_exists($target_file)) {
        $result['status'] = FALSE;
        $result['message'] = "Sorry, file already exists.";
        return $result;
    }
    if ($data["file_size"] > $mediaField['max_size']) {
        $result['status'] = FALSE;
        $result['message'] = "Sorry, your file is too large.";
        return $result;
    }
    if (!in_array($data['file_type'], $mediaField['extension'])) {
        $result['status'] = FALSE;
        $result['message'] = "Sorry, we are not allow these files type to upload.";
        return $result;
    }
    $file->move($data['file_path'], $data['file_name']);
    if (file_exists($target_file)) {
        $result['status'] = TRUE;
        $result['message'] = "The file " . $data['file_caption'] . " has been uploaded.";
        return $result;
    } else {
        $result['status'] = FALSE;
        $result['message'] = "Sorry, there was an error uploading your file.";
        return $result;
    }
}

function getAllMedia($reference_id = 0, $reference_type, $media_type = null)
{
    if (0 == $reference_id) return array();
    $query = DB::table('media_reference as m_f')
        ->join('media', 'm_f.media_id', '=', 'media.id')
        ->where('m_f.reference_id', $reference_id)
        ->where('m_f.reference_type', $reference_type);
    if (null != $media_type && is_array($media_type)) {
        $query->whereIn('media.file_type', $media_type);
    }
    return $query->select('media.*')->get();
}

function fileSizeFormat($bytes)
{
    $label = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++) ;
    return (round($bytes, 2) . " " . $label[$i]);
}

function showPrettyStatus($status)
{
    if(config('global')["STATUS_ACTIVE"] == $status) {
        return 'published';
    }
    if(config('global')["STATUS_INACTIVE"] == $status) {
        return 'draft';
    }
    if(config('global')["STATUS_PENDING"] == $status) {
        return 'pending';
    }
}

// Phone Number Validation Helper

function checkoperator($msisdn) {
    $carrierMapper = \libphonenumber\PhoneNumberToCarrierMapper::getInstance();
    $chNumber = \libphonenumber\PhoneNumberUtil::getInstance()->parse($msisdn, null);
    $operator_name=$carrierMapper->getNameForNumber($chNumber, 'en');
    return $operator_name;
}


function country($msisdn)
{
    $geoCoder = \libphonenumber\geocoding\PhoneNumberOfflineGeocoder::getInstance();;
    $gbNumber = \libphonenumber\PhoneNumberUtil::getInstance()->parse($msisdn, null);
    $country=$geoCoder->getDescriptionForNumber($gbNumber, 'en_GB', 'US');
    return $country;
}

function operator($msisdn)
{
    $carrierMapper = \libphonenumber\PhoneNumberToCarrierMapper::getInstance();
    $chNumber = \libphonenumber\PhoneNumberUtil::getInstance()->parse($msisdn, null);
    $operator_name=$carrierMapper->getNameForNumber($chNumber, 'en');
    return $operator_name;
}

function getUUID()
{
    return rand(100,999).time().rand(100,999);
}

function getAllCategories() {
    $categories = ArticleCategory::all();
    return $categories;
}

function CategoryParnet($id) {
    $category = ArticleCategory::find($id);
    if(empty($category)) {
        return "-- None --";
    }

    return $category->name;
}

function checkFav($article_id, $type) {
    $customer_id = Session::get('user_id');
    $favourite = Favourite::where('customer_id', $customer_id)->where('article_id', $article_id)->where('type', $type)->first();
    if($favourite) {
        return 'btn-active';
    }
    return;
}

