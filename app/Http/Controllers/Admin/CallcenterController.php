<?php
/**
created by z
 * time 2019-11-4 17:53:05
 */
namespace App\Http\Controllers\Admin;
use Auth;
use App\Http\Requests\StoreRequest;
use App\Models\Callcenter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
class CallcenterController extends Controller
{
    /**
     * 客服列表
     */
    public function index(StoreRequest $request){

        $kefu=Callcenter::query();

        if(true==$request->has('id')){
            $kefu->where('id','=',$request->input('id'));
        }
        if(true==$request->has('content')){
            $kefu->where('content','like','%'.$request->input('content').'%');
        }
        if(true==$request->has('creatime')){
            $creatime=$request->input('creatime');
            $start=strtotime($creatime);
            $end=strtotime('+1day',$start);
            $kefu->whereBetween('creatime',[$start,$end]);
        }

        $data=$kefu->orderBy('creatime','desc')->paginate(10)->appends($request->all());
        foreach ($data as $key =>$value){
            $data[$key]['creatime']=date("Y-m-d H:i:s",$value["creatime"]);
            $data[$key]['url']='http://'.$_SERVER['HTTP_HOST'].$value["url"];
        }
        $min=config('admin.min_date');
        return view('callcenter.list',['pager'=>$data,'min'=>$min,'input'=>$request->all()]);
    }

    /**
    编辑页
     */
    public function edit($id=0){
        $info = $id?Callcenter::find($id):[];
        if(!empty($info)){
            $info['url']='http://'.$_SERVER['HTTP_HOST'].$info["url"];
        }
        return view('callcenter.edit',['id'=>$id,'info'=>$info]);
    }

    /**
     * 用户增加保存
     */
    public function store(StoreRequest $request){
        $data=$request->all();
        $file = $request->file('url');
        if($file){

            $file_relative_path = '/callcenter/'.date('Ymd');//public下callcenter目录
            $file_path = public_path($file_relative_path);//相对路劲
            if (!is_dir($file_path)){                       //创建年月日文件夹
                mkdir($file_path);
            }
            $ext = $file->getClientOriginalExtension();   //后缀
            $img=['jpeg','jpg','png','ico'];
            if(!in_array($ext,$img)){
                return ['msg'=>'图片格式为jpeg,jpg,png,ico'];
            }

            $path = $file->getRealPath();       //真实路径
            $filename = $file_relative_path.'/'.date('Ymd').mt_rand(999,9999).'.'.$ext;//文件路径

            Storage::disk('callcenter')->put($filename, file_get_contents($path)); //存入服务器
            $data['creatime']=time();
            $data['url']=$filename;
            $insert=Callcenter::insert($data);//存入数据库
            if($insert){
                return ['msg'=>'添加成功！','status'=>1];
            }else{
                return ['msg'=>'添加失败！'];
            }
        }else{
            return ['msg'=>'请选择二维码图片！'];
        }

    }

    /**
     * 修改
     */
    public function update(StoreRequest $request){
        $data=$request->all();
        $id=$data['id'];
        unset($data['id']);
        $file = $request->file('url');
        if($file){

            $file_relative_path = '/callcenter/'.date('Ymd');//public下callcenter目录
            $file_path = public_path($file_relative_path);//相对路劲
            if (!is_dir($file_path)){                       //创建年月日文件夹
                mkdir($file_path);
            }

            $ext = $file->getClientOriginalExtension();

            $img=['jpeg','jpg','png','ico'];
            if(!in_array($ext,$img)){
                return ['msg'=>'图片格式为jpeg,jpg,png,ico'];
            }
            $info=Callcenter::find($id);
            $url=$info['url'];
            //图片存在先删除
            if(Storage::disk('callcenter')->exists($url)){
                Storage::disk('callcenter')->delete($url);
            }
            $path = $file->getRealPath();
            $filename = $file_relative_path.'/'.date('Ymd').mt_rand(999,9999).'.'.$ext;//文件路径

            Storage::disk('callcenter')->put($filename, file_get_contents($path));//上传图片
            $data['url']=$filename;
            $update2=Callcenter::where('id',$id)->update($data);
            if($update2!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }else{
            $update1=Callcenter::where('id',$id)->update(array('content'=>$data['content']));
            if($update1!==false){
                return ['msg'=>'修改成功！','status'=>1];
            }else{
                return ['msg'=>'修改失败！'];
            }
        }
    }
    /**
     * 删除
     */
    public function destroy($id){
        $res = Callcenter::where('id',$id)->delete();
        if($res){
            return ['msg'=>'删除成功！','status'=>1];
        }else{
            return ['msg'=>'删除失败！'];
        }
    }


}
