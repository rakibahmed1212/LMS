import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import MarketingLayout from '@/Layouts/MarketingLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

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

export default function CoursesIndex({ years, allYears, filters }) {
    const auth = usePage().props.auth;
    const Layout = auth.user ? AuthenticatedLayout : MarketingLayout;
    const [values, setValues] = useState({
        q: filters.q || '',
        class_year_id: filters.class_year_id || '',
    });
    const resultCount = useMemo(
        () =>
            years.reduce(
                (total, year) =>
                    total +
                    year.subjects.reduce(
                        (subjectTotal, subject) =>
                            subjectTotal + subject.courses.length,
                        0,
                    ),
                0,
            ),
        [years],
    );

    useEffect(() => {
        const timeout = setTimeout(() => {
            router.get(route('courses.index'), values, {
                preserveState: true,
                replace: true,
            });
        }, 300);

        return () => clearTimeout(timeout);
    }, [values]);

    const content = (
        <>
            <Head title="Courses" />
            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
                    <div className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="grid gap-4 lg:grid-cols-[1fr_220px_auto] lg:items-end">
                            <div>
                                <label className="label" htmlFor="course_q">
                                    Search courses
                                </label>
                                <input
                                    id="course_q"
                                    value={values.q}
                                    onChange={(e) =>
                                        setValues({ ...values, q: e.target.value })
                                    }
                                    className="input mt-1"
                                    placeholder="Search topic, lesson, worksheet or subject"
                                />
                            </div>
                            <div>
                                <label className="label" htmlFor="course_year">
                                    Year
                                </label>
                                <select
                                    id="course_year"
                                    value={values.class_year_id}
                                    onChange={(e) =>
                                        setValues({
                                            ...values,
                                            class_year_id: e.target.value,
                                        })
                                    }
                                    className="input mt-1"
                                >
                                    <option value="">All years</option>
                                    {allYears.map((year) => (
                                        <option key={year.id} value={year.id}>
                                            {year.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <p className="rounded-lg bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600">
                                {resultCount} course{resultCount === 1 ? '' : 's'}
                            </p>
                        </div>
                    </div>

                    {resultCount === 0 && (
                        <div className="rounded-lg border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">
                            No courses match this search.
                        </div>
                    )}

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
