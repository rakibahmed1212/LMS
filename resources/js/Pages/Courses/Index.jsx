import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link, usePage } from '@inertiajs/react';

function CourseCard({ course, subject }) {
    return (
        <Link
            href={route('courses.show', { course: course.slug })}
            className="group overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-cyan-200 hover:shadow-md"
        >
            <div className="aspect-[16/9] overflow-hidden bg-slate-100">
                <img
                    src={course.thumbnail || '/images/tuition-hero.png'}
                    alt=""
                    className="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                />
            </div>
            <div className="p-5">
                <div className="mb-3 flex items-center gap-2">
                    <span
                        className="h-2.5 w-2.5 rounded-full"
                        style={{ backgroundColor: subject.color }}
                    />
                    <span className="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        {subject.name}
                    </span>
                </div>
                <h3 className="text-base font-semibold text-slate-950">
                    {course.title}
                </h3>
                <p className="mt-2 line-clamp-2 text-sm leading-6 text-slate-500">
                    {course.description}
                </p>
                <div className="mt-4 flex items-center justify-between text-sm">
                    <span className="text-slate-500">
                        {course.lessons_count} lessons
                    </span>
                    <span className="font-semibold text-cyan-700">
                        View course
                    </span>
                </div>
            </div>
        </Link>
    );
}

export default function CoursesIndex({ years }) {
    const auth = usePage().props.auth;
    const Layout = auth.user ? AuthenticatedLayout : MarketingLayout;
    const content = (
        <>
            <Head title="Courses" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
                    {years.map((year) => (
                        <section key={year.id}>
                            <div className="mb-4 flex items-center gap-3">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {year.name}
                                </h3>
                                <span className="h-px flex-1 bg-slate-200" />
                            </div>
                            <div className="space-y-8">
                                {year.subjects.map((subject) => (
                                    <div key={subject.id}>
                                        <div className="mb-3 flex items-center gap-2">
                                            <span
                                                className="h-3 w-3 rounded-full"
                                                style={{
                                                    backgroundColor: subject.color,
                                                }}
                                            />
                                            <h4 className="font-semibold text-slate-800">
                                                {subject.name}
                                            </h4>
                                        </div>
                                        {subject.courses.length === 0 ? (
                                            <div className="rounded-lg border border-dashed border-slate-300 bg-white p-5 text-sm text-slate-500">
                                                No published courses yet.
                                            </div>
                                        ) : (
                                            <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                                                {subject.courses.map((course) => (
                                                    <CourseCard
                                                        key={course.id}
                                                        course={course}
                                                        subject={subject}
                                                    />
                                                ))}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </section>
                    ))}
                </div>
            </div>
        </>
    );

    return auth.user ? (
        <Layout
            header={
                <div>
                    <h2 className="text-xl font-semibold text-slate-900">
                        Course Portal
                    </h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Browse courses. Subscribe for a child to unlock full access.
                    </p>
                </div>
            }
        >
            {content}
        </Layout>
    ) : (
        <Layout>{content}</Layout>
    );
}
