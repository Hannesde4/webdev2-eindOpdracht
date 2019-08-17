<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Pledge;
use App\Models\Image;
use App\Models\Reward;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ProjectsController extends Controller
{
    public function index()
    {

        $projects = Project::all();

        foreach($projects as $project){

            // calculates how much days untill the project ends
            $project->daysToGo = Project::calculateDaysToGo($project->final_date);

            // calculates the total sum of all the pledges
            $project->allPledges = Project::calculateSumOfPledges($project->pledges);

            // calculates the total progress of all the pledges
            $project->progress = Project::calculateDonationProgress($project->goal, $project->allPledges);

            // counts the amount of unique backers
            $project->totalBackers = count(Pledge::all()->where('project_id', $project->id)->groupBy('user_id'));
        }

        return view('projects.index')->with(compact('projects'));

    }

    public function show($id)
    {
        $project = Project::where('id', $id);

        // calculates how much days untill the project ends
        $project->daysToGo = Project::calculateDaysToGo($project->first()['final_date']);


        // calculates the total sum of all the pledges
        $project->allPledges = Project::calculateSumOfPledges($project->first()['pledges']);

        // calculates the total progress of all the pledges
        $project->progress = Project::calculateDonationProgress($project->first()['goal'], $project->allPledges);


        // counts the amount of unique backers
        $project->totalBackers = count(Pledge::all()->where('project_id', $project->first()['id'])->groupBy('user_id'));

        $project->images = Image::all()->where('project_id', $id);

        $project->rewards = Reward::all()->where('project_id', $id);

        $project->id = $id;

        return view('projects.show')->with(compact('project'));
    }



    public function edit($id){

        $userId = Auth::id();
        $project = Project::find($id);

        if($userId === 1 || $userId === $project->user_id){

            $categories = Category::all();

            return view('projects.edit')->with(compact('project', 'categories'));

        }else{
            return redirect('/projects');
        }

    }

    public function update($id){

        $project = Project::find($id);

        $project->project_name = request('project_name');
        $project->project_intro = request('project_intro');
        $project->project_description = request('project_description');
        $project->category = request('project_category');
        $project->final_date = request('final_date');
        $project->goal = request('goal');

        $project->save();

        return redirect('/projects/' . $id);
    }



    public function destroy($id){
        //dd($id);

        $project = Project::findOrFail($id);

        if($project->pledges->first()){
            return back()->with([
                'notification' => 'danger',
                'message' => 'dit project kan niet verwijderd worden omdat er al donaties zijn!'
            ]);
        }else{

            $project->images = Image::all()->where('project_id', $id);

            foreach( $project->images as $image){
                $file = str_replace('storage/', '', $image->filepath) . '/' . $image->filename;

                Storage::disk('public')->delete($file);

            }

            rmdir(storage_path('app/public/project-'.$id));


            $project->images()->delete();

            $project->rewards()->delete();

            $project->delete();
            return back()->with([
                'notification' => 'success',
                'message' => 'Het project is succesvol verwijderd'
            ]);
            return redirect('/profile');
        }


    }

}
