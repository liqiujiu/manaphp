<?php
namespace Test\App {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {

    }
}

namespace Test\Blog {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {

    }
}

namespace Test\Blog2 {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {
        public function __construct($useDefaultRoutes = true)
        {
            parent::__construct($useDefaultRoutes);

            $this->add('/article/{id:\\d+}', 'article::detail');
        }
    }
}

namespace Test\Blog3 {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {
        public function __construct($useDefaultRoutes = true)
        {
            parent::__construct($useDefaultRoutes);
            $this->add('/save', array(
                'action' => 'save'
            ));

            $this->add('/edit/{id}', array(
                'action' => 'edit'
            ));

            $this->add('/about', 'about::index');
        }
    }
}

namespace Test\Path {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {

    }
}

namespace Test\Domain {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {

    }
}

namespace Test\DomainPath {

    class RouteGroup extends \ManaPHP\Mvc\Router\Group
    {

    }
}