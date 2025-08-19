 <?php  
    $title = "Openminds";
    $filename = "home";

    include_once "../app/views/partials/header.view.php";
 ?>
<div class="home-background background-gradient">
    
    <nav class="">
        <div class="name">Openminds</div>
        <div class="btns">
            <a href="<?=ROOT?>/login">log in</a>
            <a href="<?=ROOT?>/register">register</a>
        </div>
    </nav>


    <div class="hero-section">
        <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73.07 112.89"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M36.47,42.81h0l.1,14.52h.07A20.51,20.51,0,1,1,16.11,77.85H1.59a35,35,0,1,0,34.88-35Z"/><path d="M36.53,25.93H17.47A17.47,17.47,0,0,1,0,8.46V0H18.06A18.47,18.47,0,0,1,36.53,18.47Z"/><path d="M36.53,25.93H55.6A17.47,17.47,0,0,0,73.07,8.46V0H55A18.48,18.48,0,0,0,36.53,18.47Z"/></g></g></svg>
        </div>
        <div class="text-main">
        Openminds Where your learning</br> fuels a community.
        </div>
        <div class="text-sub">
        A shared space where self-learners support each other — </br>all in one focused, community-driven platform.
        </div>
        <a href="<?=ROOT?>/register" class="button">Start Your Journey</a>
    </div>

<!--     
<div class="gradient-background gradient-lavender"></div>
<div class="gradient-background gradient-blue"></div> -->



    <div class="philosophy-section">
        <div class="text">
            <div class="title">Our philosophy</div>
            <div class="prompt">
            A shared platform where learning and helping become part of the same human act. Built by the community. Sustained by kindness. Driven by the joy of growing — together.
            </div>
        </div>

        <div class="image">
            <div class="icon">
                <div class="leave1 leave">
                    <?php include  "./assets/images/leaves.svg"; ?>
                </div>
                <div class="leave2 leave">
                    <?php include  "./assets/images/leaves.svg"; ?>
                </div>
                <div class="leave3 leave">
                    <?php include  "./assets/images/leaves.svg"; ?>
                </div> 
                <div class="leave4 leave">
                    <?php include  "./assets/images/leaves.svg"; ?>
                </div>
            </div>           
            <div class="main-image">

            </div>
        </div>
    </div>



    <div class="values-section">
        <div class="text">
            What we <span>stands</span> for
        </div>

        <div class="statements">
            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/hand-heart.svg"; ?></div>
                <div class="title">
                    Grow by Giving
                </div>
                <div class="text">
                    Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.
                </div>
            </div>


            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/boxes.svg"; ?></div>
                <div class="title">
                    Everything, Together
                </div>
                <div class="text">
                Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.                </div>
            </div>


            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/hand-coins.svg"; ?></div>
                <div class="title">
                    Free, Always
                </div>
                <div class="text">
                Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.                </div>
            </div>
        </div>
    </div>



    <div class="feature-section">
        <div class="text-section">
            <div class="note-feature feature">
                <div class="feature-name tile">Note Feature</div>
                <div class="main-text">
                    All your note in<br>One place.
                </div>
                <div class="sub-text">
                with our note feature, keep you all notes in a singleplace.
                </div>
            </div>


            <div class="q&a-feature feature">
                <div class="feature-name tile">Q & A Feature</div>
                <div class="main-text">
                    Ask any question
                    from community.
                </div>
                <div class="sub-text">
                    with our q&a feature you can ask any question and there is a entire community to help you
                </div>
            </div>


            <div class="exercise-feature feature">
                <div class="feature-name tile">Exercise Feature</div>
                <div class="main-text">
                improve with
                thousands of exercises.
                </div>
                <div class="sub-text">
                    test your knowledge with community created exercises.
                </div>
            </div>

            <div class="analysis-feature feature">
                <div class="feature-name tile">Analysis Feature</div>
                <div class="main-text">
                Track your progress
                with analysis.
                </div>
                <div class="sub-text">
                    with our analysis feature you can keep track your growth and also your impact to community.
                </div>
            </div>
        </div>

        <div class="image-section">
            <div class="content-wrapper">
                <div class="square-wrapper">
                    <div class="square">

                    </div>
                </div>
                <div class="image">

                </div>
                <div class="icon">
                    <?php include  "./assets/images/leaves.svg"; ?>
                    <?php include  "./assets/images/leaves.svg"; ?>
                    <?php include  "./assets/images/leaves.svg"; ?>
                    <?php include  "./assets/images/leaves.svg"; ?>
                    <?php include  "./assets/images/leaves.svg"; ?>
                    <?php include  "./assets/images/leaves.svg"; ?>
                </div>
            </div>
        </div>
    </div>


    <div class="fqa-section">
        <div class="title">Frequently Asked Questions</div>
        <div class="question-section">

            <div class="question question1">
                <div class="card">
                    <div class="left">
                        What is your objective for this project?
                    </div>
                    <div class="right">
                        +
                    </div>
                </div>
                <div class="answer container inactive">
                Our main objective is to create a platform that makes learning and knowledge sharing more personalized, transparent, and collaborative. Instead of being a one-way content delivery system, we want to empower students, mentors, and experts to connect, track progress, and grow together with tools designed for real learning outcomes.
                </div>
            </div>

            <div class="question question2">
                <div class="card">
                    <div class="left">
                        how are we different from other platforms?
                    </div>
                    <div class="right">
                        +
                    </div>
                </div>
                <div class="answer container inactive">
                Unlike most platforms that only provide static content or one-sided lectures, our system focuses on interactive engagement and personalized tracking. Students can create notes, track their knowledge growth, and share with peers. Mentors can directly guide learners with assignments and live sessions. Experts validate content quality, and admins keep everything safe and organized. This multi-role ecosystem ensures quality, trust, and collaboration in a way that typical platforms don’t.
                </div>
            </div>

            <div class="question question3">
                <div class="card">
                    <div class="left">
                        is all features completetly free?
                    </div>
                    <div class="right">
                        +
                    </div>                                        
                </div>
                <div class="answer container inactive">
                Yes, all core features are completely free to use. Our mission is to make accessible learning available to everyone without hidden paywalls. While we may introduce optional premium services in the future (like advanced analytics or dedicated mentorship), the foundation of learning, sharing, and connecting will always remain free.
                </div>
            </div>

        </div>
    </div>


    <div class="cta-section">
        <a href="<?=ROOT?>/register" class="button btn-none">Start Your Journey</a>
        <div class="background-img">
            <?php include  "./assets/images/leaves.svg";?>
        </div>
    </div>

</div>



 <?php

    include_once "../app/views/partials/footer.view.php";

 ?>