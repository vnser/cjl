<?php
/**
 * Created by PhpStorm.
 * User: Vnser
 * Date: 2017/2/26
 * Time: 22:38
 */

namespace Lib;

class Page
{
    //分页配置项
    public $config = [
        'param' => 'page',
        'num_length' => 3,
        'theme' => [
            'class' => ['disable' => 'disabled', 'checked' => 'active'],
            'btn' => [
                'prev' => '<li class="[%class%]"><a href="[%link%]"> <span aria-hidden="true">&laquo;</span></a></li>',
                'next' => '<li class="[%class%]"><a href="[%link%]"> <span aria-hidden="true">&raquo;</span></a></li>',
                'index' => '<li class="[%class%]"><a href="[%link%]">首页</a></li>',
                'end' => '<li class="[%class%]"><a href="[%link%]">末页</a></li>',
                'list' => '<li class="[%class%]"><a href="[%link%]">[%page%]</a></li>',
            ],
            'nested' => '<div class="rows page-box"><div class="col-sm-4 page-header">共[%count%]条记录; 页码： [%this_page%]/[%page_count%]页</div><div class="col-sm-8"><nav class="page-num"><ul class="pagination"><li>[%index%][%prev%][%list%][%next%][%end%]</ul></nav></div></div>',
        ]
    ];
    //记录总数
    public $count;
    //每页记录条数
    public $list_count;
    //起始行数
    public $first_count;
    //总页数
    public $page_count;
    //当前页码
    public $this_page;
    //当前url链接
    protected $url;

    /**
     * 构造函数
     * @param int $count
     * @param int $list_count
     */
    public function __construct($count, $list_count)
    {
        //记录总条数
        $this->count = $count;
        //每页显示条数
        $this->list_count = $list_count;
        //总页码
        $this->page_count = ceil($this->count / $this->list_count);
        //当前页码
        $this->this_page = $_GET[$this->config['param']];
        //计算总页
        $this->this_page = $this->this_page <= 0 ? 1 : ($this->this_page > $this->page_count ? ($this->page_count > 0 ? $this->page_count : 1) : $this->this_page);
        //其实查询行数
        $this->first_count = ($this->this_page - 1) * $this->list_count;
//        print_r($_SERVER);

        $this->url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?'.urldecode(http_build_query(array_merge($_GET,[$this->config['param'] => '[%page%]'])));
//        var_dump($this->url);

    }

    /**
     * 设置替换URL
     * @param string $html
     * @param int $page
     * @param null|string $link
     * @return string
     */
    private function setLink($html, $page, $link = NULL)
    {
        $link = $link ?: $this->url;
        return str_replace(['[%link%]', '[%page%]'], [$link, $page], $html);
    }

    /**
     * 替换语法[%class%]
     * @param string $html
     * @param string $class
     * @return mixed
     */
    private function addClass($html, $class)
    {
        return str_replace('[%class%]', $class, $html);
    }

    /**
     * 获取分页html内容
     * @param int $list_num
     * @return string
     */
    public function getPageHtml($list_num = 0)
    {
        if ($this->count == 0)
            return '';
        $theme = $this->config['theme'];
        $list_num = $list_num ?: $this->config['num_length'];
        //list分页
        $list = '';
        if ($list_num > 0) {
            $roll = ($list_num > $this->page_count ? $this->page_count : $list_num);
            $ceil = ceil($roll / 2);
            for ($i = 1; $i <= $roll; $i++) {
                $num = $i - $ceil + $this->this_page;
                if ($this->this_page - $ceil < 1) {
                    //头页
                    $num = $i;
                }
                if ($this->this_page + $ceil > $this->page_count) {
                    //末尾
                    $num = $this->page_count - $roll + $i;
                }
                //list
                $list .= $this->addClass($this->setLink($theme['btn']['list'], $num), $this->this_page == $num ? $theme['class']['checked'] : '');
            }
        }
        //首页
        $index = '';
        if (!empty($theme['btn']['index'])) {
            $index = $this->addClass($this->setLink($theme['btn']['index'], 1, $this->this_page <= 1 ? 'javascript:;' : NULL), $this->this_page <= 1 ? $theme['class']['disable'] : '');
        }
        //末页
        $end = '';
        if (!empty($theme['btn']['end'])) {
            $end = $this->addClass($this->setLink($theme['btn']['end'], $this->page_count, $this->this_page >= $this->page_count ? 'javascript:;' : NULL), $this->this_page >= $this->page_count ? $theme['class']['disable'] : '');
        }
        //上一页
        $prev = '';
        if (!empty($theme['btn']['prev'])) {
            $prev = $this->addClass($this->setLink($theme['btn']['prev'], $this->this_page <= 1 ? 1 : $this->this_page - 1, $this->this_page <= 1 ? 'javascript:;' : NULL), $this->this_page <= 1 ? $theme['class']['disable'] : '');
        }
        //下一页
        $next = '';
        if (!empty($theme['btn']['next'])) {
            $next = $this->addClass($this->setLink($theme['btn']['next'], $this->this_page >= $this->page_count ? $this->page_count : $this->this_page + 1, $this->this_page >= $this->page_count ? 'javascript:;' : NULL), $this->this_page >= $this->page_count ? $theme['class']['disable'] : '');
        }

        return str_replace([
            '[%index%]', '[%end%]', '[%prev%]', '[%next%]', '[%list%]', '[%this_page%]', '[%page_count%]', '[%list_count%]', '[%count%]'
        ], [
            $index, $end, $prev, $next, $list, $this->this_page, $this->page_count, $this->list_count, $this->count
        ], $theme['nested']);
    }
}