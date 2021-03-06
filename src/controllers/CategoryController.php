<?php namespace Redooor\Redminportal;

class CategoryController extends BaseController {

    protected $model;

    public function __construct(Category $category)
    {
        $this->model = $category;
    }

    public function getIndex()
    {
        $categories = Category::orderBy('order', 'desc')->orderBy('name')->paginate(20);

        return \View::make('redminportal::categories/view')->with('categories', $categories);
    }

    public function getCreate()
    {
        $categories = array();

        // Add: No parent
        $categories[0] = "No parent";

        foreach (Category::all() as $category) {
            $categories[$category->id] = $category->name;
        }

        return \View::make('redminportal::categories/create')->with('categories', $categories);
    }

    public function getEdit($id)
    {
        // Find the category using the user id
        $category = Category::find($id);

        if(empty($category->options))
        {
            $category_cn = (object) array(
                'name'                  => $category->name,
                'short_description'     => $category->short_description,
                'long_description'      => $category->long_description
            );
        }
        else
        {
            $category_cn = json_decode($category->options);
        }

        $categories = array();

        // Add: No parent
        $categories[0] = "No parent";

        foreach (Category::all() as $cat) {
            if ($category->id == $cat->id) continue;
            $categories[$cat->id] = $cat->name;
        }

        return \View::make('redminportal::categories/edit')
            ->with('category', $category)
            ->with('category_cn', $category_cn)
            ->with('imageUrl', 'assets/img/categories/')
            ->with('categories', $categories);
    }

    public function postStore()
    {
        $id = \Input::get('id');

        /*
         * Validate
         */
        $rules = array(
            'image'                 => 'mimes:jpg,jpeg,png,gif|max:500',
            'name'                  => 'required|unique:categories,name' . (isset($id) ? ',' . $id : ''),
            'short_description'     => 'required',
            'order'                 => 'required|min:0',
        );

        $validation = \Validator::make(\Input::all(), $rules);

        if( $validation->passes() )
        {
            $name               = \Input::get('name');
            $short_description  = \Input::get('short_description');
            $long_description   = \Input::get('long_description');
            $image              = \Input::file('image');
            $active             = (\Input::get('active') == '' ? FALSE : TRUE);
            $order              = \Input::get('order');
            $parent_id          = \Input::get('parent_id');

            $cn_name               = \Input::get('cn_name');
            $cn_short_description  = \Input::get('cn_short_description');
            $cn_long_description   = \Input::get('cn_long_description');

            $options = array(
                'name'                  => $cn_name,
                'short_description'     => $cn_short_description,
                'long_description'      => $cn_long_description
            );

            $category = (isset($id) ? Category::find($id) : new Category);
            $category->name = $name;
            $category->short_description = $short_description;
            $category->long_description = $long_description;
            $category->active = $active;
            $category->order = $order;
            $category->category_id = $parent_id;
            $category->options = json_encode($options);

            $category->save();

            if(\Input::hasFile('image'))
            {
                // Delete all existing images for edit
                if(isset($id)) $category->deleteAllImages();

                //set the name of the file
                $originalFilename = $image->getClientOriginalName();
                $filename = str_replace(' ', '', $name) . \Str::random(20) .'.'. \File::extension($originalFilename);

                //Upload the file
                $isSuccess = $image->move('assets/img/categories', $filename);

                if( $isSuccess )
                {
                    // create photo
                    $newimage = new Image;
                    $newimage->path = $filename;

                    // save photo to the loaded model
                    $category->images()->save($newimage);
                }
            }

        }//if it validate
        else {
            if(isset($id))
            {
                return \Redirect::to('admin/categories/edit/' . $id)->withErrors($validation)->withInput();
            }
            else
            {
                return \Redirect::to('admin/categories/create')->withErrors($validation)->withInput();
            }
        }

        return \Redirect::to('admin/categories');
    }

    public function getDelete($id)
    {
        // Find the category using the user id
        $category = Category::find($id);

        // Find if there's any child
        $children = Category::where('category_id', $id)->count();

        if ($children > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The category '" . $category->name . "' cannot be deleted because it has " . $children . " children categories.");
            return \Redirect::to('/admin/categories')->withErrors($errors);
        }

        // Check in use by media
        $medias = Media::where('category_id', $id)->get();
        if (count($medias) > 0) {
            $errors = new \Illuminate\Support\MessageBag;
            $errors->add('deleteError', "The category '" . $category->name . "' cannot be deleted because it is in used.");
            return \Redirect::to('/admin/categories')->withErrors($errors);
        }

        // Delete all images first
        $category->deleteAllImages();

        // Delete the category
        $category->delete();

        return \Redirect::to('admin/categories');
    }

}
