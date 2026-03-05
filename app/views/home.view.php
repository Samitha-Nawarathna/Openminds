<?php  
    $title = "Openminds";
    $filename = "home";
    $no_navbar = true;

    include_once "../app/views/partials/header.view.php";
?>

<div class="home-background background-gradient">
    
    <nav class="nav-bar">
        <div class="name">Openminds</div>
        <div class="btns">
            <a href="<?=ROOT?>/login">log in</a>
            <a href="<?=ROOT?>/register">register</a>
        </div>
    </nav>

    <div class="hero-section">
        
        <div class="hero-content gradient-lavender">
            <div class="hero-badge tag-pill green">
                🎉 100% Free & Open Source
            </div>

            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 73.07 112.89"><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path d="M36.47,42.81h0l.1,14.52h.07A20.51,20.51,0,1,1,16.11,77.85H1.59a35,35,0,1,0,34.88-35Z"/><path d="M36.53,25.93H17.47A17.47,17.47,0,0,1,0,8.46V0H18.06A18.47,18.47,0,0,1,36.53,18.47Z"/><path d="M36.53,25.93H55.6A17.47,17.47,0,0,0,73.07,8.46V0H55A18.48,18.48,0,0,0,36.53,18.47Z"/></g></g></svg>
            </div>

            <h1 class="text-main">
                Stop juggling
                in multiple platforms.<br/>
                Learn in <span class="highlight">one place.</span>
            </h1>
            
            <div class="text-sub">
                Your entire learning journey—notes, questions, exercises, and progress—finally connected. No more scattered tabs. No more lost progress.
            </div>

            <div class="features-mini">
                <div class="feature-item">Organized Notes</div>
                <div class="feature-item">Ask Questions</div>
                <div class="feature-item">Practice Exercises</div>
                <div class="feature-item">Track Progress</div>
            </div>

            <div class="cta-group">
                <a href="<?=ROOT?>/register" class="button no-style-link">Start Learning Free</a>
                <a href="#how-it-works" class="button button-secondary no-style-link">See How It Works</a>
            </div>

            <p class="open-source-note">
                <strong>Open source forever.</strong> Built by learners, for learners.
            </p>
        </div>
    </div>

    <div class="unified-learning-section">
        
        <div class="unified-message">
            <h2 class="unified-message-title">
                All four phases of learning.<br/>
                <span class="emphasis">Finally unified</span> in one platform.
            </h2>
            <p class="unified-message-subtitle">
                No more context switching. No more lost progress. Just continuous, connected learning from first note to mastery.
            </p>
        </div>

        <div class="learning-grid">
            <div class="learning-card notes">
                <div class="card-content">
                    <div class="card-icon">📝</div>
                    <h3 class="card-title">Notes</h3>
                    <p class="card-description">
                        Organize your thoughts and learning materials in one place. Structure your knowledge base.
                    </p>
                </div>
            </div>
            <div class="learning-card questions">
                <div class="card-content">
                    <div class="card-icon">💬</div>
                    <h3 class="card-title">Questions</h3>
                    <p class="card-description">
                        Get unstuck fast. Ask the community and receive answers from fellow learners.
                    </p>
                </div>
            </div>
            <div class="learning-card exercises">
                <div class="card-content">
                    <div class="card-icon">✏️</div>
                    <h3 class="card-title">Exercises</h3>
                    <p class="card-description">
                        Practice what you learn. Test your understanding with community-created exercises.
                    </p>
                </div>
            </div>
            <div class="learning-card analysis">
                <div class="card-content">
                    <div class="card-icon">📊</div>
                    <h3 class="card-title">Analysis</h3>
                    <p class="card-description">
                        Track your complete learning journey. Identify patterns and celebrate growth.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- ROLES SECTION -->
    <div class="roles-section">
        <div class="roles-header">
            <h2 class="roles-title">Level up your learning journey</h2>
            <p class="roles-subtitle">From curious student to subject authority. Your contributions fuel your growth.</p>
        </div>

        <div class="roles-grid">
            <!-- Student -->
            <div class="role-card role-student">
                <div class="role-icon-wrapper">
                    <div class="role-icon">🎓</div>
                </div>
                <div class="role-content">
                    <h3 class="role-name">Student</h3>
                    <div class="role-badge">Entry Level</div>
                    <p class="role-description">
                        Create and edit notes, Test questions, and attempt community exercises.
                    </p>
                </div>
            </div>

            <div class="role-connector"></div>

            <!-- Mentor -->
            <div class="role-card role-mentor">
                 <div class="role-icon-wrapper">
                    <div class="role-icon">💡</div>
                </div>
                <div class="role-content">
                    <h3 class="role-name">Mentor</h3>
                    <div class="role-badge">Reach 50 XP</div>
                    <p class="role-description">
                        All student features, plus ability to create, share, and delete exercises.
                    </p>
                </div>
            </div>

            <div class="role-connector"></div>

            <!-- Expert -->
            <div class="role-card role-expert">
                 <div class="role-icon-wrapper">
                    <div class="role-icon">👤</div>
                </div>
                <div class="role-content">
                    <h3 class="role-name">Expert</h3>
                    <div class="role-badge">Subject Proof</div>
                    <p class="role-description">
                        Approved by Admin. Browse and validate exercise requests in your field.
                    </p>
                </div>
            </div>

            <div class="role-connector"></div>

            <!-- Admin -->
            <div class="role-card role-admin">
                 <div class="role-icon-wrapper">
                    <div class="role-icon">⚙️</div>
                </div>
                <div class="role-content">
                    <h3 class="role-name">Admin</h3>
                    <div class="role-badge">System Guardian</div>
                    <p class="role-description">
                        Review expert requests, ban/unban accounts, and moderate all content.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="philosophy-section" id="how-it-works">
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
            <div class="main-image"></div>
        </div>
    </div>

    <!-- <div class="values-section">
        <div class="text">
            What we <span>stand</span> for
        </div>

        <div class="statements">
            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/hand-heart.svg"; ?></div>
                <div class="title">Grow by Giving</div>
                <div class="text">Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.</div>
            </div>

            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/boxes.svg"; ?></div>
                <div class="title">Everything, Together</div>
                <div class="text">Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.</div>
            </div>

            <div class="statement-card container">
                <div class="icon"><?php include  "./assets/images/hand-coins.svg"; ?></div>
                <div class="title">Free, Always</div>
                <div class="text">Learning thrives when shared. We focus on contribution—helping others as a way to deepen your own understanding.</div>
            </div>
        </div>
    </div> -->

    <div class="feature-section">
    <div class="text-section">
        
        <div class="note-feature feature">
            <div class="feature-name tag-pill green">Note Feature</div>
            <div class="text-main">All your notes in<br>One place.</div>
            <div class="text-sub">Keep all your notes neatly organized in one place.</div>
        </div>

        <div class="q&a-feature feature">
            <div class="feature-name tag-pill green">Q & A Feature</div>
            <div class="text-main">Ask any question from community.</div>
            <div class="text-sub">Ask questions and get help from a supportive learning community.</div>
        </div>

        <div class="exercise-feature feature">
            <div class="feature-name tag-pill green">Exercise Feature</div>
            <div class="text-main">improve with thousands of exercises.</div>
            <div class="text-sub">Test your knowledge with exercises created by the community.</div>
        </div>

        <div class="analysis-feature feature">
            <div class="feature-name tag-pill green">Analysis Feature</div>
            <div class="text-main">Track your progress with analysis.</div>
            <div class="text-sub">Track your progress and see how your contributions impact the community.</div>
        </div>

    </div>



        <div class="image-section">
            <div class="content-wrapper">
                <div class="square-wrapper">
                    <div class="square"></div>
                </div>
                <div class="image"></div>
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
                    <div class="left">What is your objective for this project?</div>
                    <div class="right">+</div>
                </div>
                <div class="answer container inactive">
                Our main objective is to create a platform that makes learning and knowledge sharing more personalized, transparent, and collaborative.
                </div>
            </div>
            <div class="question question2">
                <div class="card">
                    <div class="left">how are we different from other platforms?</div>
                    <div class="right">+</div>
                </div>
                <div class="answer container inactive">
                Unlike most platforms that only provide static content or one-sided lectures, our system focuses on interactive engagement and personalized tracking.
                </div>
            </div>
            <div class="question question3">
                <div class="card">
                    <div class="left">is all features completely free?</div>
                    <div class="right">+</div>                                        
                </div>
                <div class="answer container inactive">
                Yes, all core features are completely free to use. Our mission is to make accessible learning available to everyone without hidden paywalls.
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